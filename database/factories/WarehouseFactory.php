<?php

namespace Database\Factories;

use App\Enums\Warehouse\WarehouseStatus;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('WH-###-???'),
            'name' => fake()->company().' Warehouse',
            'status' => fake()->randomElement(WarehouseStatus::cases()),
        ];
    }
}
