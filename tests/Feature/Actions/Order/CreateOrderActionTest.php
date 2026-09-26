<?php

use App\Actions\Order\CreateOrderAction;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\travelTo;

it('sets the order expiration to fifteen minutes after creation', function () {
    travelTo('2026-09-26 12:00:00');
    $sku = Sku::factory()->create(['price' => 1_250]);
    Inventory::factory()->for($sku)->create(['quantity' => 10]);

    $order = app(CreateOrderAction::class)->handle([
        'items' => [['sku_id' => $sku->id, 'quantity' => 1]],
    ]);

    expect((string) $order->getRawOriginal('expires_at'))->toBe('2026-09-26 12:15:00');
});

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
    $sku = Sku::factory()->create([
        'code' => 'SKU-LIMITED',
        'name' => 'Limited SKU',
        'price' => 1_250,
    ]);
    $inventory = Inventory::factory()->for($sku)->create([
        'quantity' => 10,
        'reserved_quantity' => 8,
    ]);

    expect(fn () => app(CreateOrderAction::class)->handle([
        'items' => [['sku_id' => $sku->id, 'quantity' => 3]],
    ]))->toThrow(
        ValidationException::class,
        'SKU SKU-LIMITED (Limited SKU) does not have enough available stock for 3 units.',
    );

    expect($inventory->refresh()->reserved_quantity)->toBe(8);
    expect(Order::query()->count())->toBe(0);
    expect(OrderItem::query()->count())->toBe(0);
    expect(InventoryReservation::query()->count())->toBe(0);
});
