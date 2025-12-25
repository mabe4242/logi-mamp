<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inbound extends Model
{
    protected $fillable = [
        'inbound_no',
        'status',
        'expected_date',
        'received_at',
        'supplier_name',
        'note',
        'created_by_admin_id',
    ];

    protected $casts = [
        'status' => 'integer',
        'expected_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InboundItem::class);
    }

    // 既存の admins テーブルを利用
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }
}
