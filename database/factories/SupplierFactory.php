<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'code' => 'SUPP' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->company() . '株式会社',
            'contact_name' => fake()->optional()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'postal_code' => fake()->optional()->postcode(),
            'address1' => fake()->optional()->prefecture() . fake()->optional()->city(),
            'address2' => fake()->optional()->streetAddress(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
