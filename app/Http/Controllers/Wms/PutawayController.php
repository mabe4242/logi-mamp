<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use App\Models\Location;
use App\Models\PutawayLine;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PutawayController extends Controller
{
    /**
     * 入庫待ち一覧（WAITING_PUTAWAY）
     */
    public function index(Request $request)
    {
        $query = InboundPlan::query()
            ->with('supplier')
            ->where('status', 'WAITING_PUTAWAY');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('supplier', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
        }

        $plans = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('wms.putaway.index', compact('plans'));
    }

    /**
     * 入庫画面
     */
    public function show(InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'WAITING_PUTAWAY') {
            return redirect()->route('putaway.index')
                ->withErrors(['status' => 'この入荷予定は入庫待ちではありません。']);
        }

        $inbound_plan->load(['supplier', 'lines.product']);

        $locations = Location::orderBy('code')->get(['id', 'code', 'name']);

        // 直近入庫ログ
        $recentPutaways = PutawayLine::where('inbound_plan_id', $inbound_plan->id)
            ->with(['location'])
            ->latest()->limit(10)->get();

        return view('wms.putaway.show', compact('inbound_plan', 'locations', 'recentPutaways'));
    }

    /**
     * 入庫実行（ 明細行 × ロケーション × 数量 ）
     */
    public function store(Request $request, InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'WAITING_PUTAWAY') {
            return back()->withErrors(['status' => '入庫できる状態ではありません。']);
        }

        $validated = $request->validate([
            'line_id' => 'required|integer|exists:inbound_plan_lines,id',
            'location_id' => 'required|integer|exists:locations,id',
            'qty' => 'required|integer|min:1',
        ]);

        $line = InboundPlanLine::with('product')->findOrFail($validated['line_id']);

        if ($line->inbound_plan_id !== $inbound_plan->id) {
            abort(404);
        }

        $remaining = max(0, (int)$line->received_qty - (int)$line->putaway_qty);
        if ($validated['qty'] > $remaining) {
            return back()->withErrors([
                'qty' => "入庫可能数量を超えています。入庫残: {$remaining}",
            ]);
        }

        DB::transaction(function () use ($inbound_plan, $line, $validated) {
            // 1) 入庫実績
            PutawayLine::create([
                'inbound_plan_id' => $inbound_plan->id,
                'inbound_plan_line_id' => $line->id,
                'location_id' => $validated['location_id'],
                'qty' => $validated['qty'],
                'putaway_by_admin_id' => auth('admin')->id(),
            ]);

            // 2) 明細の入庫済数を増加
            $line->increment('putaway_qty', $validated['qty']);

            // 3) stocks を増加（product_id × location_id で upsert）
            $stock = Stock::firstOrCreate(
                // ① 検索条件
                [
                    'product_id' => $line->product_id,
                    'location_id' => $validated['location_id'],
                ],
                // ② 作成時の追加カラム（※見つからなかった場合だけ使う）
                [
                    'on_hand_qty' => 0,
                    'reserved_qty' => 0,
                ]
            );

            // 「現在庫」を増やす
            $stock->increment('on_hand_qty', $validated['qty']);

            // 4) 全明細が入庫済なら COMPLETED
            $allDone = $inbound_plan->lines()
                ->selectRaw('SUM(received_qty - putaway_qty) as remaining')
                ->value('remaining');

            if ((int)$allDone <= 0) {
                $inbound_plan->update(['status' => 'COMPLETED']);
            }
        });

        return redirect()
            ->route('putaway.show', $inbound_plan)
            ->with('success', "入庫しました：{$line->product->name}（+{$validated['qty']}）");
    }

    /**
     * 入庫完了処理
     */
    public function complete(InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'WAITING_PUTAWAY') {
            return back()->withErrors(['status' => '完了にできる状態ではありません。']);
        }

        $remaining = $inbound_plan->lines()
            ->selectRaw('SUM(received_qty - putaway_qty) as remaining')
            ->value('remaining');

        if ((int)$remaining > 0) {
            return back()->withErrors(['status' => "まだ入庫残があります（残: {$remaining}）"]);
        }

        $inbound_plan->update(['status' => 'COMPLETED']);

        return redirect()->route('putaway.index')->with('success', '入庫を完了しました');
    }
}
