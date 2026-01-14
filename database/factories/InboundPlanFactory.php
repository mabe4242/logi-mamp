<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\InboundPlan;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InboundPlan>
 */
class InboundPlanFactory extends Factory
{
    protected $model = InboundPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'supplier_id' => Supplier::factory(),
            'planned_date' => fake()->date(),
            'status' => 'DRAFT',
            'created_by_admin_id' => Admin::factory(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
