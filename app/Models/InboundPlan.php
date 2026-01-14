<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboundPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'planned_date',
        'status',
        'created_by_admin_id',
        'note',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    // 仕入先
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // 作成者（admin）
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // 予定明細
    public function lines(): HasMany
    {
        return $this->hasMany(InboundPlanLine::class);
    }

    // 検品ログ
    public function receivingLogs(): HasMany
    {
        return $this->hasMany(ReceivingLog::class);
    }

    // 入庫実績
    public function putawayLines(): HasMany
    {
        return $this->hasMany(PutawayLine::class);
    }

    /**
     * よく使うスコープ（任意）
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
