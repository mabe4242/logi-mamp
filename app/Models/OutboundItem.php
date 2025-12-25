<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundItem extends Model
{
    protected $fillable = [
        'outbound_id',
        'product_id',
        'location_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(Outbound::class);
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
