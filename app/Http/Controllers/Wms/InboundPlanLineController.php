<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use Illuminate\Http\Request;

class InboundPlanLineController extends Controller
{
    public function store(Request $request, InboundPlan $inbound_plan)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'planned_qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        if ($inbound_plan->status !== 'DRAFT') {
            return back()->withErrors(['status' => '確定後は明細を追加できません（下書きに戻す運用が必要です）。']);
        }

        InboundPlanLine::updateOrCreate(
            ['inbound_plan_id' => $inbound_plan->id, 'product_id' => $validated['product_id']],
            [
                'planned_qty' => $validated['planned_qty'],
                'note' => $validated['note'] ?? null,
            ]
        );

        return redirect()
            ->route('inbound-plans.show', $inbound_plan)
            ->with('success', '明細を追加しました');
    }

    public function update(Request $request, InboundPlan $inbound_plan, InboundPlanLine $line)
    {
        $validated = $request->validate([
            'planned_qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        if ($inbound_plan->status !== 'DRAFT') {
            return back()->withErrors(['status' => '確定後は明細を編集できません。']);
        }

        // 念のため：別の予定の明細が来たら弾く
        if ($line->inbound_plan_id !== $inbound_plan->id) {
            abort(404);
        }

        $line->update($validated);

        return redirect()
            ->route('inbound-plans.show', $inbound_plan)
            ->with('success', '明細を更新しました');
    }

    public function destroy(InboundPlan $inbound_plan, InboundPlanLine $line)
    {
        if ($inbound_plan->status !== 'DRAFT') {
            return back()->withErrors(['status' => '確定後は明細を削除できません。']);
        }

        if ($line->inbound_plan_id !== $inbound_plan->id) {
            abort(404);
        }

        $line->delete();

        return redirect()
            ->route('inbound-plans.show', $inbound_plan)
            ->with('success', '明細を削除しました');
    }
}
