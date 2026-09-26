<?php

use App\Actions\Order\ExpireOrderAction;
use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Enums\Order\OrderStatus;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use DateTimeInterface;
use Illuminate\Support\Str;

use function Pest\Laravel\travelTo;

/** @return array{inventory: Inventory, order: Order, reservation: InventoryReservation} */
function createOrderReadyForExpiration(DateTimeInterface $expiresAt): array
{
    $sku = Sku::factory()->create(['price' => 1_250]);
    $inventory = Inventory::factory()->for($sku)->create([
        'quantity' => 10,
        'reserved_quantity' => 3,
    ]);
    $order = Order::query()->create([
        'order_number' => 'ORD-'.Str::upper(Str::random(8)),
        'status' => OrderStatus::Pending,
        'total_amount' => 3_750,
        'expires_at' => $expiresAt,
    ]);
    $orderItem = OrderItem::query()->create([
        'order_id' => $order->id,
        'sku_id' => $sku->id,
        'quantity' => 3,
        'unit_price' => 1_250,
    ]);
    $reservation = InventoryReservation::query()->create([
        'inventory_id' => $inventory->id,
        'order_item_id' => $orderItem->id,
        'quantity' => 3,
    ]);

    return compact('inventory', 'order', 'reservation');
}

it('expires a due pending order and releases its reserved inventory', function () {
    travelTo('2026-09-26 12:00:00');
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForExpiration(now());

    $expiredOrder = app(ExpireOrderAction::class)->handle($order);

    expect($expiredOrder->status)->toBe(OrderStatus::Expired);
    expect($order->refresh()->status)->toBe(OrderStatus::Expired);
    expect($inventory->refresh()->reserved_quantity)->toBe(0);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Released);
});

it('leaves a pending order unchanged before its expiration time', function () {
    travelTo('2026-09-26 12:00:00');
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForExpiration(now()->addSecond());

    $pendingOrder = app(ExpireOrderAction::class)->handle($order);

    expect($pendingOrder->status)->toBe(OrderStatus::Pending);
    expect($inventory->refresh()->reserved_quantity)->toBe(3);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Reserved);
});

it('leaves a non-pending order unchanged', function () {
    travelTo('2026-09-26 12:00:00');
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForExpiration(now()->subSecond());
    $order->update(['status' => OrderStatus::Cancelled]);
    $inventory->update(['reserved_quantity' => 0]);
    $reservation->update(['status' => InventoryReservationStatus::Released]);

    $cancelledOrder = app(ExpireOrderAction::class)->handle($order);

    expect($cancelledOrder->status)->toBe(OrderStatus::Cancelled);
    expect($inventory->refresh()->reserved_quantity)->toBe(0);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Released);
});

it('keeps expiration idempotent when expiring the same order twice', function () {
    travelTo('2026-09-26 12:00:00');
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForExpiration(now()->subSecond());

    app(ExpireOrderAction::class)->handle($order);
    $expiredOrder = app(ExpireOrderAction::class)->handle($order);

    expect($expiredOrder->status)->toBe(OrderStatus::Expired);
    expect($inventory->refresh()->reserved_quantity)->toBe(0);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Released);
});
