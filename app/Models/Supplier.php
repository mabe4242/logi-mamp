<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact_name',
        'phone',
        'email',
        'postal_code',
        'address1',
        'address2',
        'note',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function inboundPlans(): HasMany
    {
        return $this->hasMany(InboundPlan::class);
    }
}
