<?php

use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CreateOrderAction;
use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Enums\Order\OrderStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Sku;

it('marks a pending order as cancelled', function () {
    $sku = Sku::factory()->create(['price' => 1_250]);
    Inventory::factory()->for($sku)->create(['quantity' => 10]);
    $order = app(CreateOrderAction::class)->handle([
        'items' => [['sku_id' => $sku->id, 'quantity' => 3]],
    ]);

    $cancelledOrder = app(CancelOrderAction::class)->handle($order);

    expect($cancelledOrder->status)->toBe(OrderStatus::Cancelled);
    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
});

it('releases reserved inventory when cancelling an order', function () {
    $sku = Sku::factory()->create(['price' => 1_250]);
    $inventory = Inventory::factory()->for($sku)->create(['quantity' => 10]);
    $order = app(CreateOrderAction::class)->handle([
        'items' => [['sku_id' => $sku->id, 'quantity' => 3]],
    ]);
    $reservation = $order->items()->sole()->reservations()->sole();

    app(CancelOrderAction::class)->handle($order);

    expect($inventory->refresh()->reserved_quantity)->toBe(0);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Released);
});

it('keeps cancellation idempotent when cancelling the same order twice', function () {
    $sku = Sku::factory()->create(['price' => 1_250]);
    $inventory = Inventory::factory()->for($sku)->create(['quantity' => 10]);
    $order = app(CreateOrderAction::class)->handle([
        'items' => [['sku_id' => $sku->id, 'quantity' => 3]],
    ]);
    $reservation = $order->items()->sole()->reservations()->sole();

    app(CancelOrderAction::class)->handle($order);

    $cancelledOrder = app(CancelOrderAction::class)->handle($order);

    expect($cancelledOrder->is($order))->toBeTrue();
    expect($cancelledOrder->status)->toBe(OrderStatus::Cancelled);
    expect($inventory->refresh()->reserved_quantity)->toBe(0);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Released);
});

it('leaves a paid order unchanged', function () {
    $order = Order::query()->create([
        'order_number' => 'ORD-PAID-CANCEL',
        'status' => OrderStatus::Paid,
        'total_amount' => 1_250,
        'expires_at' => now()->addMinutes(15),
    ]);

    $cancelledOrder = app(CancelOrderAction::class)->handle($order);

    expect($cancelledOrder->status)->toBe(OrderStatus::Paid);
    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
});
