<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function inbounds(): HasMany
    {
        return $this->hasMany(Inbound::class, 'created_by_admin_id');
    }

    public function outbounds(): HasMany
    {
        return $this->hasMany(Outbound::class, 'created_by_admin_id');
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class, 'created_by_admin_id');
    }

    public function createdInboundPlans(): HasMany
    {
        return $this->hasMany(InboundPlan::class, 'created_by_admin_id');
    }

    public function receivingLogs(): HasMany
    {
        return $this->hasMany(ReceivingLog::class, 'scanned_by_admin_id');
    }

    public function putawayLines(): HasMany
    {
        return $this->hasMany(PutawayLine::class, 'putaway_by_admin_id');
    }

    public function shipmentPlans()
    {
        return $this->hasMany(ShipmentPlan::class, 'created_by_admin_id');
    }

    public function pickingLogs()
    {
        return $this->hasMany(PickingLog::class, 'picked_by_admin_id');
    }

    public function shippingLogs()
    {
        return $this->hasMany(ShippingLog::class, 'shipped_by_admin_id');
    }
}
