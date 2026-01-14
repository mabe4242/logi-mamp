<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $prefectures = ['東京都', '大阪府', '京都府', '神奈川県', '埼玉県', '千葉県', '愛知県', '福岡県'];
        
        return [
            'code' => 'CUST' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->company() . '株式会社',
            'contact_name' => fake()->optional()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'postal_code' => fake()->optional()->postcode(),
            'address1' => fake()->optional()->randomElement($prefectures) . fake()->optional()->city(),
            'address2' => fake()->optional()->streetAddress(),
            'shipping_method' => fake()->optional()->randomElement(['ヤマト', '佐川', '日本郵便', '西濃']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
