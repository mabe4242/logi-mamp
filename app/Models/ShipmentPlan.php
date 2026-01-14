<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'planned_ship_date',
        'status',
        'carrier',
        'tracking_no',
        'note',
        'created_by_admin_id',
    ];

    protected $casts = [
        'planned_ship_date' => 'date',
    ];

    /* ========= Relations ========= */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ShipmentPlanLine::class);
    }

    public function pickingLogs(): HasMany
    {
        return $this->hasMany(PickingLog::class);
    }

    public function shippingLogs(): HasMany
    {
        return $this->hasMany(ShippingLog::class);
    }

    /* ========= Scopes ========= */

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
