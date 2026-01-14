<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wms\ScanReceivingRequest;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
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
        $query = InboundPlan::query()->with('supplier')
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

        // 直近ログ
        $recentLogs = ReceivingLog::where('inbound_plan_id', $inbound_plan->id)
            ->latest()->limit(10)->get();

        return view('wms.receiving.show', compact('inbound_plan', 'recentLogs'));
    }

    /**
     * 検品スキャン（入力フォームで代替中）
     * 現場を想定してJANコードでもskuコードでも検品できるようにしている
     * barcode or sku を受け取って、該当明細の received_qty を +1
     */
    public function scan(ScanReceivingRequest $request, InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'RECEIVING') {
            return back()->withErrors(['status' => '検品できる状態ではありません。']);
        }

        $code = trim($request->validated()['code']);

        // 入荷予定に紐づく商品明細を特定
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

        // 予定数超えを防止
        if ($line->received_qty >= $line->planned_qty) {
            return back()->withErrors([
                'code' => "この商品はすでに予定数まで検品済みです（{$line->product->name}）",
            ]);
        }

        DB::transaction(function () use ($inbound_plan, $line, $code) {
            ReceivingLog::create([
                'inbound_plan_id' => $inbound_plan->id,
                'inbound_plan_line_id' => $line->id,
                'scanned_code' => $code,
                'qty' => 1,  //これマジックナンバー
                'scanned_by_admin_id' => auth('admin')->id(),
            ]);

            // 明細側カウントアップ
            $line->increment('received_qty', 1); //これマジックナンバー
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

        // 1件も検品されてないなら止める
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
