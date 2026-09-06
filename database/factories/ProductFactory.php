<?php

namespace Database\Factories;

use App\Models\Product;
use App\Product\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(ProductStatus::cases()),
        ];
    }
}
