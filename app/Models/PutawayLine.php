<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PutawayLine extends Model
{
    protected $fillable = [
        'inbound_plan_id',
        'inbound_plan_line_id',
        'location_id',
        'qty',
        'putaway_by_admin_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inboundPlan(): BelongsTo
    {
        return $this->belongsTo(InboundPlan::class);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(InboundPlanLine::class, 'inbound_plan_line_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function putawayByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'putaway_by_admin_id');
    }
}
