<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingLog extends Model
{
    protected $fillable = [
        'shipment_plan_id',
        'carrier',
        'tracking_no',
        'shipped_at',
        'shipped_by_admin_id',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
    ];

    /* ========= Relations ========= */

    public function shipmentPlan(): BelongsTo
    {
        return $this->belongsTo(ShipmentPlan::class);
    }

    public function shippedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'shipped_by_admin_id');
    }
}
