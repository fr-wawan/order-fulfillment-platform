<?php

use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Enums\Order\OrderStatus;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Sku;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;

it('seeds a populated and internally consistent demo dataset', function () {
    app(DatabaseSeeder::class)->run();

    expect(User::query()->count())->toBe(10)
        ->and(Product::query()->count())->toBe(24)
        ->and(Sku::query()->count())->toBe(72)
        ->and(Warehouse::query()->count())->toBe(6)
        ->and(Inventory::query()->count())->toBe(432)
        ->and(Order::query()->count())->toBe(75)
        ->and(OrderItem::query()->count())->toBeGreaterThanOrEqual(75)
        ->and(Order::query()->where('status', OrderStatus::Pending)->count())->toBe(60)
        ->and(Order::query()->where('status', OrderStatus::Cancelled)->count())->toBe(15);

    $reservedQuantity = Inventory::query()->sum('reserved_quantity');
    $activeReservationQuantity = InventoryReservation::query()
        ->where('status', InventoryReservationStatus::Reserved)
        ->sum('quantity');

    expect($reservedQuantity)->toBe($activeReservationQuantity)
        ->and(Inventory::query()
            ->whereColumn('reserved_quantity', '>', 'quantity')
            ->doesntExist())->toBeTrue();
});
