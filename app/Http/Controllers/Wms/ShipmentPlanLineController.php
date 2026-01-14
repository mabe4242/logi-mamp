<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wms\StoreShipmentPlanLineRequest;
use App\Http\Requests\Wms\UpdateShipmentPlanLineRequest;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use Illuminate\Http\Request;

class ShipmentPlanLineController extends Controller
{
    public function store(StoreShipmentPlanLineRequest $request, ShipmentPlan $shipment_plan)
    {
        if ($shipment_plan->status !== 'PLANNED') {
            return back()->withErrors(['status' => '確定後は編集できません']);
        }

        $validated = $request->validated();

        ShipmentPlanLine::updateOrCreate(
            [
                'shipment_plan_id' => $shipment_plan->id,
                'product_id' => $validated['product_id'],
            ],
            ['planned_qty' => $validated['planned_qty']]
        );

        return back()->with('success', '明細を追加しました');
    }

    public function update(UpdateShipmentPlanLineRequest $request, ShipmentPlan $shipment_plan, ShipmentPlanLine $line)
    {
        $line->update($request->validated());

        return back()->with('success', '明細を更新しました');
    }

    public function destroy(ShipmentPlan $shipment_plan, ShipmentPlanLine $line)
    {
        $line->delete();
        return back()->with('success', '明細を削除しました');
    }
}
