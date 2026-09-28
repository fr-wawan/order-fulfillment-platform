<?php

use App\Actions\Order\FulfillOrderAction;
use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Enums\Order\OrderStatus;
use App\Exceptions\InventoryReservation\UnexpectedInventoryReservationStatusException;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;

/** @return array{inventory: Inventory, order: Order, reservation: InventoryReservation} */
function createOrderReadyForFulfillment(
    OrderStatus $status = OrderStatus::Paid,
    InventoryReservationStatus $reservationStatus = InventoryReservationStatus::Reserved,
): array {
    $sku = Sku::factory()->create(['price' => 1_250]);
    $inventory = Inventory::factory()->for($sku)->create([
        'quantity' => 10,
        'reserved_quantity' => 3,
    ]);
    $order = Order::query()->create([
        'order_number' => 'ORD-FULFILL',
        'status' => $status,
        'total_amount' => 2_500,
        'expires_at' => now()->addMinutes(15),
    ]);
    $orderItem = OrderItem::query()->create([
        'order_id' => $order->id,
        'sku_id' => $sku->id,
        'quantity' => 2,
        'unit_price' => 1_250,
    ]);
    $reservation = InventoryReservation::query()->create([
        'inventory_id' => $inventory->id,
        'order_item_id' => $orderItem->id,
        'quantity' => 2,
        'status' => $reservationStatus,
    ]);

    return compact('inventory', 'order', 'reservation');
}

it('fulfills a paid order and deducts its reserved inventory', function () {
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForFulfillment();

    $fulfilledOrder = app(FulfillOrderAction::class)->handle($order);

    expect($fulfilledOrder->status)->toBe(OrderStatus::Fulfilled);
    expect($order->refresh()->status)->toBe(OrderStatus::Fulfilled);
    expect($inventory->refresh())
        ->quantity->toBe(8)
        ->reserved_quantity->toBe(1);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Fulfilled);
});

it('does not deduct inventory again when fulfilling the same order twice', function () {
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForFulfillment();

    app(FulfillOrderAction::class)->handle($order);
    $fulfilledOrder = app(FulfillOrderAction::class)->handle($order);

    expect($fulfilledOrder->status)->toBe(OrderStatus::Fulfilled);
    expect($inventory->refresh())
        ->quantity->toBe(8)
        ->reserved_quantity->toBe(1);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Fulfilled);
});

it('leaves a non-paid order and its inventory unchanged', function () {
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForFulfillment(OrderStatus::Pending);

    $unchangedOrder = app(FulfillOrderAction::class)->handle($order);

    expect($unchangedOrder->status)->toBe(OrderStatus::Pending);
    expect($inventory->refresh())
        ->quantity->toBe(10)
        ->reserved_quantity->toBe(3);
    expect($reservation->refresh()->status)->toBe(InventoryReservationStatus::Reserved);
});

it('rolls back fulfillment when a reservation is no longer reserved', function (
    InventoryReservationStatus $reservationStatus,
) {
    ['inventory' => $inventory, 'order' => $order, 'reservation' => $reservation]
        = createOrderReadyForFulfillment(
            reservationStatus: $reservationStatus,
        );

    expect(fn () => app(FulfillOrderAction::class)->handle($order))
        ->toThrow(UnexpectedInventoryReservationStatusException::class);

    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
    expect($inventory->refresh())
        ->quantity->toBe(10)
        ->reserved_quantity->toBe(3);
    expect($reservation->refresh()->status)->toBe($reservationStatus);
})->with([
    'released reservation' => InventoryReservationStatus::Released,
    'fulfilled reservation' => InventoryReservationStatus::Fulfilled,
]);
