<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Sku;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skus = Sku::query()->get();

        Warehouse::query()->each(function (Warehouse $warehouse) use ($skus) {
            foreach ($skus as $sku) {
                Inventory::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'sku_id' => $sku->id,
                    'quantity' => fake()->numberBetween(100, 500),
                ]);
            }
        });
    }
}
