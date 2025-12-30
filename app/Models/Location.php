<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
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

    public function putawayLines(): HasMany
    {
        return $this->hasMany(PutawayLine::class);
    }
}
