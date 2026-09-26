<?php

namespace Database\Seeders;

use App\Enums\Product\ProductStatus;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory(24)
            ->sequence(
                ['status' => ProductStatus::Active],
                ['status' => ProductStatus::Active],
                ['status' => ProductStatus::Active],
                ['status' => ProductStatus::Inactive],
            )
            ->create();
    }
}
