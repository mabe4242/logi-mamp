<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use App\Models\Product;
use App\Models\ReceivingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InboundReceiveController extends Controller
{
    /**
     * 検品対象（RECEIVING）の入荷予定一覧
     */
    public function index(Request $request)
    {
        $query = InboundPlan::query()
            ->with('supplier')
            ->where('status', 'RECEIVING');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('supplier', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
        }

        $plans = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('wms.receiving.index', compact('plans'));
    }

    /**
     * 検品画面
     */
    public function show(InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'RECEIVING') {
            return redirect()->route('receiving.index')
                ->withErrors(['status' => 'この入荷予定は検品対象（入荷作業中）ではありません。']);
        }

        $inbound_plan->load(['supplier', 'lines.product']);

        // 直近ログ（見えると気持ちいい）
        $recentLogs = ReceivingLog::where('inbound_plan_id', $inbound_plan->id)
            ->latest()->limit(10)->get();

        return view('wms.receiving.show', compact('inbound_plan', 'recentLogs'));
    }

    /**
     * 検品スキャン（入力欄で代替）
     * barcode or sku を受け取って、該当明細の received_qty を +1
     */
    public function scan(Request $request, InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'RECEIVING') {
            return back()->withErrors(['status' => '検品できる状態ではありません。']);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:255', // barcode or sku
        ]);

        $code = trim($validated['code']);

        // ① 入荷予定に紐づく商品明細を特定
        $line = InboundPlanLine::query()
            ->where('inbound_plan_id', $inbound_plan->id)
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

        // 予定数超えを禁止（MVP安全運用）
        if ($line->received_qty >= $line->planned_qty) {
            return back()->withErrors([
                'code' => "この商品はすでに予定数まで検品済みです（{$line->product->name}）",
            ]);
        }

        DB::transaction(function () use ($inbound_plan, $line, $code) {
            // ログ
            ReceivingLog::create([
                'inbound_plan_id' => $inbound_plan->id,
                'inbound_plan_line_id' => $line->id,
                'scanned_code' => $code,
                'qty' => 1,
                'scanned_by_admin_id' => auth('admin')->id(),
            ]);

            // 明細側カウントアップ
            $line->increment('received_qty', 1);
        });

        return redirect()
            ->route('receiving.show', $inbound_plan)
            ->with('success', "検品しました：{$line->product->name}（+1）");
    }

    /**
     * 検品完了 → 入庫待ちへ
     */
    public function finish(InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'RECEIVING') {
            return back()->withErrors(['status' => '検品完了にできる状態ではありません。']);
        }

        // 1件も検品されてないなら止める（任意）
        $receivedTotal = $inbound_plan->lines()->sum('received_qty');
        if ($receivedTotal <= 0) {
            return back()->withErrors(['status' => '検品済み数量が0です。検品してから完了してください。']);
        }

        $inbound_plan->update(['status' => 'WAITING_PUTAWAY']);

        return redirect()
            ->route('receiving.index')
            ->with('success', '検品を完了しました（入庫待ちへ移動）');
    }
}
