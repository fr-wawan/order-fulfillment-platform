<?php

namespace Database\Factories;

use App\Enums\Sku\SkuStatus;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sku>
 */
class SkuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'code' => fake()->unique()->bothify('SKU-####-????'),
            'name' => fake()->words(3, true),
            'price' => fake()->numberBetween(0, 10_000_000),
            'status' => fake()->randomElement(SkuStatus::cases()),
        ];
    }
}
