<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use App\Models\Customer;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = ShipmentPlan::query()->with('customer');

        // 検索：出荷先名（customers.name）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->whereHas('customer', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            });
        }

        // 検索：出荷予定日（planned_ship_date）
        if ($request->filled('planned_ship_date')) {
            $query->whereDate('planned_ship_date', $request->planned_ship_date);
        }

        $plans = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('wms.shipment_plans.index', compact('plans'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('wms.shipment_plans.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'planned_ship_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        $plan = ShipmentPlan::create([
            ...$validated,
            'status' => 'PLANNED',
            'created_by_admin_id' => auth('admin')->id(),
        ]);

        return redirect()
            ->route('shipment-plans.show', $plan)
            ->with('success', '出荷予定を作成しました。');
    }

    public function show(ShipmentPlan $shipment_plan)
    {
        $shipment_plan->load(['customer', 'lines.product']);
        $products = Product::orderBy('id', 'desc')->get();

        return view('wms.shipment_plans.show', compact('shipment_plan', 'products'));
    }

    public function edit(ShipmentPlan $shipment_plan)
    {
        $customers = Customer::orderBy('name')->get();
        return view('wms.shipment_plans.edit', compact('shipment_plan', 'customers'));
    }

    public function update(Request $request, ShipmentPlan $shipment_plan)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'planned_ship_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        $shipment_plan->update($validated);

        return redirect()
            ->route('shipment-plans.show', $shipment_plan)
            ->with('success', '更新しました');
    }

    public function destroy(ShipmentPlan $shipment_plan)
    {
        $shipment_plan->delete();

        return redirect()
            ->route('shipment-plans.index')
            ->with('success', '削除しました');
    }

    /**
     * 在庫引当（reserved_qty を増やす）
     */
    public function allocate(ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PLANNED') {
            return back()->withErrors(['status' => '引当できる状態ではありません']);
        }

        DB::transaction(function () use ($shipment_plan) {

            foreach ($shipment_plan->lines as $line) {
                $required = $line->planned_qty;

                // 商品全体の在庫（ロケーション合算）
                $totalStock = Stock::where('product_id', $line->product_id)
                    ->selectRaw('SUM(on_hand_qty - reserved_qty) as available')
                    ->value('available');

                if ($totalStock < $required) {
                    throw new \Exception(
                        "在庫不足：{$line->product->name}（必要 {$required}, 在庫 {$totalStock}）"
                    );
                }

                // 引当（まずは単純に合計だけ予約）
                Stock::where('product_id', $line->product_id)
                    ->orderBy('on_hand_qty', 'desc')
                    ->get()
                    ->each(function ($stock) use (&$required) {
                        if ($required <= 0) return;

                        $canReserve = $stock->on_hand_qty - $stock->reserved_qty;
                        if ($canReserve <= 0) return;

                        $reserveQty = min($canReserve, $required);
                        $stock->increment('reserved_qty', $reserveQty);
                        $required -= $reserveQty;
                    });
            }

            $shipment_plan->update(['status' => 'ALLOCATED']);
        });

        return redirect()
            ->route('shipment-plans.show', $shipment_plan)
            ->with('success', '在庫を引当しました');
    }

    /**
     * 引当解除
     */
    public function deallocate(ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'ALLOCATED') {
            return back()->withErrors(['status' => '解除できる状態ではありません']);
        }

        DB::transaction(function () use ($shipment_plan) {

            foreach ($shipment_plan->lines as $line) {
                $release = $line->planned_qty;

                Stock::where('product_id', $line->product_id)
                    ->where('reserved_qty', '>', 0)
                    ->orderByDesc('reserved_qty')
                    ->get()
                    ->each(function ($stock) use (&$release) {
                        if ($release <= 0) return;

                        $releaseQty = min($stock->reserved_qty, $release);
                        $stock->decrement('reserved_qty', $releaseQty);
                        $release -= $releaseQty;
                    });
            }

            $shipment_plan->update(['status' => 'PLANNED']);
        });

        return redirect()
            ->route('shipment-plans.show', $shipment_plan)
            ->with('success', '在庫引当を解除しました');
    }
}
