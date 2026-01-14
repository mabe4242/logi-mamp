<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'location_id',
        'on_hand_qty',
        'reserved_qty',
    ];

    protected $casts = [
        'on_hand_qty' => 'integer',
        'reserved_qty' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // 使いやすい在庫（引当可能数）
    public function getAvailableQtyAttribute(): int
    {
        return max(0, (int)$this->on_hand_qty - (int)$this->reserved_qty);
    }
}
