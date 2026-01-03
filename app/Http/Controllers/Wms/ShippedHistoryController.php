<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\ShipmentPlan;
use App\Models\ShippingLog;
use Illuminate\Http\Request;

class ShippedHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ShipmentPlan::query()
            ->with('customer')
            ->where('status', 'SHIPPED');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
        }

        if ($request->filled('planned_ship_date')) {
            $query->whereDate('planned_ship_date', $request->planned_ship_date);
        }

        if ($request->filled('tracking_no')) {
            $query->where('tracking_no', 'like', "%{$request->tracking_no}%");
        }

        $plans = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('wms.shipped_histories.index', compact('plans'));
    }

    public function show(ShipmentPlan $shipment_plan)
    {
        $this->guardShipped($shipment_plan);

        $shipment_plan->load(['customer', 'lines.product']);

        $shippingLogs = ShippingLog::where('shipment_plan_id', $shipment_plan->id)
            ->latest()->get();

        return view('wms.shipped_histories.show', compact('shipment_plan', 'shippingLogs'));
    }

    // 任意：履歴から帳票を再表示（②のビューを流用）
    public function invoice(ShipmentPlan $shipment_plan)
    {
        $this->guardShipped($shipment_plan);

        $shipment_plan->load(['customer', 'lines.product']);
        return view('wms.allocated_shipments.invoice', compact('shipment_plan'));
    }

    public function label(ShipmentPlan $shipment_plan)
    {
        $this->guardShipped($shipment_plan);

        $shipment_plan->load(['customer', 'lines.product']);
        return view('wms.allocated_shipments.label', compact('shipment_plan'));
    }

    private function guardShipped(ShipmentPlan $shipment_plan): void
    {
        if ($shipment_plan->status !== 'SHIPPED') {
            abort(404);
        }
    }
}
