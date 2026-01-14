<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'sku' => 'SKU' . str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'barcode' => fake()->optional()->ean13(),
            'name' => fake()->words(3, true) . '商品',
            'unit' => fake()->randomElement(['個', '箱', '本', '枚', 'kg', 'g']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
