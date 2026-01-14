<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $area = fake()->randomElement(['A', 'B', 'C', 'D']);
        $shelf = str_pad((string) fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT);

        return [
            'code' => $area . '-' . $shelf,
            'name' => fake()->optional()->words(2, true) . 'ロケーション',
            'note' => fake()->optional()->sentence(),
        ];
    }
}
