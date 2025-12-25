<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outbound extends Model
{
    protected $fillable = [
        'outbound_no',
        'status',
        'ship_date',
        'shipped_at',
        'order_no',
        'customer_name',
        'note',
        'created_by_admin_id',
    ];

    protected $casts = [
        'status' => 'integer',
        'ship_date' => 'date',
        'shipped_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OutboundItem::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }
}
