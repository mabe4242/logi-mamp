<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'unit',
        'image_path',
        'note',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function inboundItems(): HasMany
    {
        return $this->hasMany(InboundItem::class);
    }

    public function outboundItems(): HasMany
    {
        return $this->hasMany(OutboundItem::class);
    }

    public function stockAdjustmentItems(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function inboundPlanLines(): HasMany
    {
        return $this->hasMany(InboundPlanLine::class);
    }

    public function shipmentPlanLines()
    {
        return $this->hasMany(ShipmentPlanLine::class);
    }
}
