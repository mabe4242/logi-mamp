<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundItem extends Model
{
    protected $fillable = [
        'inbound_id',
        'product_id',
        'location_id',
        'expected_qty',
        'received_qty',
    ];

    protected $casts = [
        'expected_qty' => 'integer',
        'received_qty' => 'integer',
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
