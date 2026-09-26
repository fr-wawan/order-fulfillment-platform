<?php

namespace Database\Seeders;

use App\Enums\Sku\SkuStatus;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkuSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::query()->each(function (Product $product) {
            Sku::factory(3)
                ->for($product)
                ->sequence(
                    ['status' => SkuStatus::Active],
                    ['status' => SkuStatus::Active],
                    ['status' => SkuStatus::Inactive],
                )
                ->create();
        });
    }
}
