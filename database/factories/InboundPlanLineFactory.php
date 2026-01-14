<?php

namespace Database\Factories;

use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InboundPlanLine>
 */
class InboundPlanLineFactory extends Factory
{
    protected $model = InboundPlanLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'inbound_plan_id' => InboundPlan::factory(),
            'product_id' => Product::factory(),
            'planned_qty' => fake()->numberBetween(1, 100),
            'received_qty' => 0,
            'putaway_qty' => 0,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
