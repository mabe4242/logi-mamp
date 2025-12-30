<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboundPlanLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inbound_plan_id',
        'product_id',
        'planned_qty',
        'received_qty',
        'putaway_qty',
        'note',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function inboundPlan(): BelongsTo
    {
        return $this->belongsTo(InboundPlan::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function receivingLogs(): HasMany
    {
        return $this->hasMany(ReceivingLog::class, 'inbound_plan_line_id');
    }

    public function putawayLines(): HasMany
    {
        return $this->hasMany(PutawayLine::class, 'inbound_plan_line_id');
    }

    /**
     * 予定残（検品残）
     */
    public function getRemainingToReceiveAttribute(): int
    {
        return max(0, (int)$this->planned_qty - (int)$this->received_qty);
    }

    /**
     * 入庫残（入庫すべき残数）
     */
    public function getRemainingToPutawayAttribute(): int
    {
        return max(0, (int)$this->received_qty - (int)$this->putaway_qty);
    }
}
