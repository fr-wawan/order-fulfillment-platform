<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Sku;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'sku_id' => Sku::factory(),
            'quantity' => fake()->numberBetween(0, 1000),
        ];
    }
}
