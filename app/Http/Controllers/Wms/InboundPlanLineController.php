<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wms\StoreInboundPlanLineRequest;
use App\Http\Requests\Wms\UpdateInboundPlanLineRequest;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use Illuminate\Http\Request;

class InboundPlanLineController extends Controller
{
    public function store(StoreInboundPlanLineRequest $request, InboundPlan $inbound_plan)
    {
        $validated = $request->validated();

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

    public function update(UpdateInboundPlanLineRequest $request, InboundPlan $inbound_plan, InboundPlanLine $line)
    {
        if ($inbound_plan->status !== 'DRAFT') {
            return back()->withErrors(['status' => '確定後は明細を編集できません。']);
        }

        // 念のため：別の予定の明細が来たら弾く
        if ($line->inbound_plan_id !== $inbound_plan->id) {
            abort(404);
        }

        $line->update($request->validated());

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
