<?php

namespace Database\Seeders;

use App\Enums\Warehouse\WarehouseStatus;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::factory(6)
            ->sequence(
                ['status' => WarehouseStatus::Active],
                ['status' => WarehouseStatus::Active],
                ['status' => WarehouseStatus::Active],
                ['status' => WarehouseStatus::Active],
                ['status' => WarehouseStatus::Active],
                ['status' => WarehouseStatus::Inactive],
            )
            ->create();
    }
}
