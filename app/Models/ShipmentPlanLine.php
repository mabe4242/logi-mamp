<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentPlanLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shipment_plan_id',
        'product_id',
        'planned_qty',
        'picked_qty',
        'shipped_qty',
        'note',
    ];

    /* ========= Relations ========= */

    public function shipmentPlan(): BelongsTo
    {
        return $this->belongsTo(ShipmentPlan::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function pickingLogs(): HasMany
    {
        return $this->hasMany(PickingLog::class);
    }

    /* ========= Accessors ========= */

    // ピッキング残
    public function getRemainingToPickAttribute(): int
    {
        return max(0, $this->planned_qty - $this->picked_qty);
    }

    // 出荷残
    public function getRemainingToShipAttribute(): int
    {
        return max(0, $this->picked_qty - $this->shipped_qty);
    }
}
