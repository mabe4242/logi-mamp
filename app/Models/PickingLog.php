<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingLog extends Model
{
    protected $fillable = [
        'shipment_plan_id',
        'shipment_plan_line_id',
        'scanned_code',
        'qty',
        'picked_by_admin_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /* ========= Relations ========= */

    public function shipmentPlan(): BelongsTo
    {
        return $this->belongsTo(ShipmentPlan::class);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ShipmentPlanLine::class, 'shipment_plan_line_id');
    }

    public function pickedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'picked_by_admin_id');
    }
}
