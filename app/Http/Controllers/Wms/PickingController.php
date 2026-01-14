<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wms\ScanPickingRequest;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use App\Models\PickingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PickingController extends Controller
{
    /**
     * ピッキング対象（PICKING）の出荷一覧
     */
    public function index(Request $request)
    {
        $query = ShipmentPlan::query()
            ->with('customer')
            ->where('status', 'PICKING');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
        }

        $plans = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('wms.picking.index', compact('plans'));
    }

    /**
     * ピッキング画面
     */
    public function show(ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PICKING') {
            return redirect()->route('picking.index')
                ->withErrors(['status' => 'この出荷予定はピッキング対象ではありません。']);
        }

        $shipment_plan->load(['customer', 'lines.product']);

        $recentLogs = PickingLog::where('shipment_plan_id', $shipment_plan->id)
            ->latest()->limit(10)->get();

        return view('wms.picking.show', compact('shipment_plan', 'recentLogs'));
    }

    /**
     * スキャン（入力）で picked_qty を +1
     */
    public function scan(ScanPickingRequest $request, ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PICKING') {
            return back()->withErrors(['status' => 'ピッキングできる状態ではありません。']);
        }

        $code = trim($request->validated()['code']);

        $line = ShipmentPlanLine::query()
            ->where('shipment_plan_id', $shipment_plan->id)
            ->whereHas('product', function ($q) use ($code) {
                $q->where('barcode', $code)->orWhere('sku', $code);
            })
            ->with('product')
            ->first();

        if (!$line) {
            return back()->withErrors([
                'code' => "予定に存在しない商品です（barcode/SKU: {$code}）",
            ])->withInput();
        }

        if ($line->picked_qty >= $line->planned_qty) {
            return back()->withErrors([
                'code' => "この商品はすでに予定数までピッキング済みです（{$line->product->name}）",
            ]);
        }

        DB::transaction(function () use ($shipment_plan, $line, $code) {
            PickingLog::create([
                'shipment_plan_id' => $shipment_plan->id,
                'shipment_plan_line_id' => $line->id,
                'scanned_code' => $code,
                'qty' => 1,
                'picked_by_admin_id' => auth('admin')->id(),
            ]);

            $line->increment('picked_qty', 1);
        });

        return redirect()
            ->route('picking.show', $shipment_plan)
            ->with('success', "ピッキングしました：{$line->product->name}（+1）");
    }

    /**
     * ピッキング完了 → PACKING（④へ）
     */
    public function finish(ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PICKING') {
            return back()->withErrors(['status' => '完了にできる状態ではありません。']);
        }

        $remaining = $shipment_plan->lines()
            ->selectRaw('SUM(planned_qty - picked_qty) as remaining')
            ->value('remaining');

        if ((int)$remaining > 0) {
            return back()->withErrors(['status' => "まだピッキング残があります（残: {$remaining}）"]);
        }

        $shipment_plan->update(['status' => 'PACKING']);

        return redirect()
            ->route('picking.index')
            ->with('success', 'ピッキングを完了しました（出荷作業へ）');
    }
}
