<?php

use App\Enums\Order\OrderStatus;
use App\Jobs\Order\ExpireOrderJob;
use App\Models\Order;

use function Pest\Laravel\travelTo;

it('expires the order identified by its id', function () {
    travelTo('2026-09-26 12:00:00');
    $order = Order::query()->create([
        'order_number' => 'ORD-JOB-DUE',
        'status' => OrderStatus::Pending,
        'total_amount' => 1_000,
        'expires_at' => now(),
    ]);

    ExpireOrderJob::dispatchSync($order->id);

    expect($order->refresh()->status)->toBe(OrderStatus::Expired);
});
