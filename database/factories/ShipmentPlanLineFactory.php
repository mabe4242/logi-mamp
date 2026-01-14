<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShipmentPlanLine>
 */
class ShipmentPlanLineFactory extends Factory
{
    protected $model = ShipmentPlanLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'shipment_plan_id' => ShipmentPlan::factory(),
            'product_id' => Product::factory(),
            'planned_qty' => fake()->numberBetween(1, 100),
            'picked_qty' => 0,
            'shipped_qty' => 0,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
