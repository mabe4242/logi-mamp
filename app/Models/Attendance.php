<?php

namespace App\Models;

use App\Models\UserBreak;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status'
    ];

    protected $casts = [
        'date'      => 'date',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(UserBreak::class);
    }

    // 進行中（未終了）の休憩を1件返すヘルパ
    public function openBreak(): ?UserBreak
    {
        return $this->breaks()->whereNull('break_end')->latest('break_start')->first();
    }
}
