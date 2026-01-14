<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\ShipmentPlan;
use App\Models\ShippingLog;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingController extends Controller
{
    public function index(Request $request)
    {
        $query = ShipmentPlan::query()
            ->with('customer')
            ->where('status', 'PACKING');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
        }

        $plans = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('wms.packing.index', compact('plans'));
    }

    public function show(ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PACKING') {
            return redirect()->route('packing.index')
                ->withErrors(['status' => 'この出荷予定は出荷作業対象ではありません。']);
        }

        $shipment_plan->load(['customer', 'lines.product']);

        $recentLogs = ShippingLog::where('shipment_plan_id', $shipment_plan->id)
            ->latest()->limit(10)->get();

        // ここに書くな！Enumsに切り出して
        $carriers = [
            'ヤマト',
            '佐川',
            '日本郵便',
            '西濃',
            'その他',
        ];

        return view('wms.packing.show', compact('shipment_plan', 'recentLogs', 'carriers'));
    }

    /**
     * 送り状スキャン（tracking_no保存）
     */
    public function scanLabel(Request $request, ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PACKING') {
            return back()->withErrors(['status' => '出荷作業できる状態ではありません。']);
        }

        $validated = $request->validate([
            'tracking_no' => 'required|string|max:255',
        ]);

        $shipment_plan->update([
            'tracking_no' => trim($validated['tracking_no']),
        ]);

        return redirect()
            ->route('packing.show', $shipment_plan)
            ->with('success', '送り状番号を登録しました');
    }

    /**
     * 運送会社設定（carrier保存）
     */
    public function setCarrier(Request $request, ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PACKING') {
            return back()->withErrors(['status' => '出荷作業できる状態ではありません。']);
        }

        $validated = $request->validate([
            'carrier' => 'required|string|max:50',
        ]);

        $shipment_plan->update([
            'carrier' => $validated['carrier'],
        ]);

        return redirect()
            ->route('packing.show', $shipment_plan)
            ->with('success', '運送会社を設定しました');
    }

    /**
     * 出荷完了（在庫減算）
     */
    public function ship(Request $request, ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PACKING') {
            return back()->withErrors(['status' => '出荷完了できる状態ではありません。']);
        }

        // 最小運用：送り状＆運送会社必須
        if (empty($shipment_plan->tracking_no)) {
            return back()->withErrors(['tracking_no' => '送り状番号が未登録です。']);
        }
        if (empty($shipment_plan->carrier)) {
            return back()->withErrors(['carrier' => '運送会社が未設定です。']);
        }

        $shipment_plan->load(['lines.product']);

        DB::transaction(function () use ($shipment_plan) {

            foreach ($shipment_plan->lines as $line) {
                // 今回出荷する数量：ピック済 - 出荷済（差分）
                $toShip = max(0, (int)$line->picked_qty - (int)$line->shipped_qty);
                if ($toShip <= 0) {
                    continue;
                }

                // 商品の在庫（ロケーション合算）を確認
                $availableOnHand = (int) Stock::where('product_id', $line->product_id)
                    ->sum('on_hand_qty');

                $totalReserved = (int) Stock::where('product_id', $line->product_id)
                    ->sum('reserved_qty');

                if ($totalReserved < $toShip) {
                    throw new \Exception("予約在庫が不足しています：{$line->product->name}");
                }
                if ($availableOnHand < $toShip) {
                    throw new \Exception("現在庫が不足しています：{$line->product->name}");
                }

                // 在庫減算：まず reserved を減らしつつ、on_hand も減らす
                // ※ ロケーション単位のピック元は未管理なので、ここではロケーション順に減らす
                $remaining = $toShip;

                $stocks = Stock::where('product_id', $line->product_id)
                    ->where('on_hand_qty', '>', 0)
                    ->orderBy('on_hand_qty', 'desc')
                    ->lockForUpdate()
                    ->get();

                foreach ($stocks as $stock) {
                    if ($remaining <= 0) break;

                    $take = min($stock->on_hand_qty, $remaining);

                    // on_hand を減らす
                    $stock->decrement('on_hand_qty', $take);

                    // reserved も同じだけ減らす（reservedが足りない場合は0まで）
                    $decReserved = min($stock->reserved_qty, $take);
                    if ($decReserved > 0) {
                        $stock->decrement('reserved_qty', $decReserved);
                    } else {
                        // ここに来るのは理論上少ないが、保険として残り予約を他ロケから減らす
                        // → reserved総量チェックは上でしているので、最後に帳尻が合う
                    }

                    $remaining -= $take;
                }

                // reservedの帳尻合わせ（ロケ跨ぎで残っている予約を減らす）
                if ($remaining < $toShip) {
                    $reservedToClear = $toShip;

                    $reserveStocks = Stock::where('product_id', $line->product_id)
                        ->where('reserved_qty', '>', 0)
                        ->orderByDesc('reserved_qty')
                        ->lockForUpdate()
                        ->get();

                    foreach ($reserveStocks as $rs) {
                        if ($reservedToClear <= 0) break;
                        $dec = min($rs->reserved_qty, $reservedToClear);
                        $rs->decrement('reserved_qty', $dec);
                        $reservedToClear -= $dec;
                    }
                }

                // 明細の出荷済を更新
                $line->increment('shipped_qty', $toShip);
            }

            // 出荷ログ
            ShippingLog::create([
                'shipment_plan_id' => $shipment_plan->id,
                'carrier' => $shipment_plan->carrier,
                'tracking_no' => $shipment_plan->tracking_no,
                'shipped_at' => now(),
                'shipped_by_admin_id' => auth('admin')->id(),
            ]);

            // ステータス更新
            $shipment_plan->update(['status' => 'SHIPPED']);
        });

        return redirect()
            ->route('packing.index')
            ->with('success', '出荷完了しました（在庫を減算しました）');
    }
}
