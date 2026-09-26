<?php

use App\Actions\Order\CreateOrderAction;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use Illuminate\Validation\ValidationException;

it('reserves inventory for normalized order items', function () {
    $sku = Sku::factory()->create(['price' => 1_250]);
    $inventory = Inventory::factory()->for($sku)->create([
        'quantity' => 10,
        'reserved_quantity' => 2,
    ]);

    $order = app(CreateOrderAction::class)->handle([
        'items' => [
            ['sku_id' => $sku->id, 'quantity' => 2],
            ['sku_id' => $sku->id, 'quantity' => 3],
        ],
    ]);

    $orderItem = $order->items()->sole();

    expect($inventory->refresh()->reserved_quantity)->toBe(7);
    $this->assertDatabaseHas('inventory_reservations', [
        'inventory_id' => $inventory->id,
        'order_item_id' => $orderItem->id,
        'quantity' => 5,
    ]);
});

it('rolls back the order when inventory has insufficient available stock', function () {
    $sku = Sku::factory()->create(['price' => 1_250]);
    $inventory = Inventory::factory()->for($sku)->create([
        'quantity' => 10,
        'reserved_quantity' => 8,
    ]);

    expect(fn () => app(CreateOrderAction::class)->handle([
        'items' => [['sku_id' => $sku->id, 'quantity' => 3]],
    ]))->toThrow(ValidationException::class, 'No inventory has the required stocks');

    expect($inventory->refresh()->reserved_quantity)->toBe(8);
    expect(Order::query()->count())->toBe(0);
    expect(OrderItem::query()->count())->toBe(0);
    expect(InventoryReservation::query()->count())->toBe(0);
});
