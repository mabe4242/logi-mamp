<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\ShipmentPlan;
use Illuminate\Http\Request;

class AllocatedShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = ShipmentPlan::query()
            ->with('customer')
            ->where('status', 'ALLOCATED');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('customer', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('planned_ship_date')) {
            $query->whereDate('planned_ship_date', $request->planned_ship_date);
        }

        $plans = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('wms.allocated_shipments.index', compact('plans'));
    }

    public function show(ShipmentPlan $shipment_plan)
    {
        $this->guardAllocated($shipment_plan);

        $shipment_plan->load(['customer', 'lines.product']);

        return view('wms.allocated_shipments.show', compact('shipment_plan'));
    }

    public function invoice(ShipmentPlan $shipment_plan)
    {
        $this->guardAllocated($shipment_plan);

        $shipment_plan->load(['customer', 'lines.product']);

        // まずはHTML表示（印刷できる）
        return view('wms.allocated_shipments.invoice', compact('shipment_plan'));
    }

    public function label(ShipmentPlan $shipment_plan)
    {
        $this->guardAllocated($shipment_plan);

        $shipment_plan->load(['customer', 'lines.product']);

        // まずはHTML表示（印刷できる）
        return view('wms.allocated_shipments.label', compact('shipment_plan'));
    }

    public function startPicking(ShipmentPlan $shipment_plan)
    {
        $this->guardAllocated($shipment_plan);

        $shipment_plan->update(['status' => 'PICKING']);

        return redirect()
            ->route('allocated-shipments.index')
            ->with('success', 'ピッキングを開始しました（ピッキング開始へ）');
    }

    private function guardAllocated(ShipmentPlan $shipment_plan): void
    {
        if ($shipment_plan->status !== 'ALLOCATED') {
            abort(404);
        }
    }
}
