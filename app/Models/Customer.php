<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
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
        'shipping_method',
        'note',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function shipmentPlans()
    {
        return $this->hasMany(ShipmentPlan::class);
    }
}
