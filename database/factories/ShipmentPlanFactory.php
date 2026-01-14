<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\ShipmentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShipmentPlan>
 */
class ShipmentPlanFactory extends Factory
{
    protected $model = ShipmentPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'customer_id' => Customer::factory(),
            'planned_ship_date' => fake()->optional()->date(),
            'status' => 'PLANNED',
            'carrier' => null,
            'tracking_no' => null,
            'created_by_admin_id' => Admin::factory(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
