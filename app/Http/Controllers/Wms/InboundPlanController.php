<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InboundPlan;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InboundPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = InboundPlan::query()->with('supplier');

        // 検索（仕入先名 / ステータス / 日付）
        // なぜここだけ無名関数も出てくるのかというと、リレーション先のsupplierに検索条件をかけてるから！
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('supplier', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('planned_date')) {
            $query->whereDate('planned_date', $request->planned_date);
        }

        $inboundPlans = $query
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // ここに定数のステータスを書いてるのはダメです！Enumsに切り出して！
        $statuses = [
            'DRAFT' => '下書き',
            'RECEIVING' => '入荷作業中（検品）',
            'WAITING_PUTAWAY' => '入庫待ち',
            'COMPLETED' => '完了',
            'CANCELED' => 'キャンセル',
        ];

        return view('wms.inbound_plans.index', compact('inboundPlans', 'statuses'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        return view('wms.inbound_plans.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'planned_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        $plan = InboundPlan::create([
            'supplier_id' => $validated['supplier_id'],
            'planned_date' => $validated['planned_date'] ?? null,
            'note' => $validated['note'] ?? null,
            'status' => 'DRAFT',
            'created_by_admin_id' => auth('admin')->id(),
        ]);

        return redirect()
            ->route('inbound-plans.show', $plan)
            ->with('success', '入荷予定を作成しました。次に明細（商品）を追加してください。');
    }

    public function show(InboundPlan $inbound_plan)
    {
        $inbound_plan->load(['supplier', 'lines.product']);

        // 明細追加用
        $suppliers = null; // showでは不要（create/edit用）
        return view('wms.inbound_plans.show', compact('inbound_plan'));
    }

    public function edit(InboundPlan $inbound_plan)
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        return view('wms.inbound_plans.edit', compact('inbound_plan', 'suppliers'));
    }

    public function update(Request $request, InboundPlan $inbound_plan)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'planned_date' => 'nullable|date',
            'note' => 'nullable|string',
            'status' => 'required|string|max:30',
        ]);

        $inbound_plan->update($validated);

        return redirect()
            ->route('inbound-plans.show', $inbound_plan)
            ->with('success', '入荷予定を更新しました');
    }

    public function destroy(InboundPlan $inbound_plan)
    {
        $inbound_plan->delete();

        return redirect()
            ->route('inbound-plans.index')
            ->with('success', '入荷予定を削除しました');
    }

    /**
     * 入荷予定の確定（作業開始）
     * DRAFT -> RECEIVING
     */
    public function confirm(InboundPlan $inbound_plan)
    {
        if ($inbound_plan->status !== 'DRAFT') {
            return back()->with('success', 'すでに確定済みです');
        }

        if ($inbound_plan->lines()->count() === 0) {
            return back()->withErrors(['lines' => '明細が1件もありません。商品を追加してから確定してください。']);
        }

        $inbound_plan->update(['status' => 'RECEIVING']);

        return redirect()
            ->route('inbound-plans.show', $inbound_plan)
            ->with('success', '入荷予定を確定しました（入荷作業中へ）');
    }
}
