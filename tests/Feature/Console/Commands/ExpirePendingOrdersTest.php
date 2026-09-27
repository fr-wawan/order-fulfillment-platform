<?php

use App\Enums\Order\OrderStatus;
use App\Jobs\Order\ExpireOrderJob;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\travelTo;

it('dispatches expiration jobs only for due pending orders', function () {
    travelTo('2026-09-26 12:00:00');
    $dueOrder = Order::query()->create([
        'order_number' => 'ORD-DUE',
        'status' => OrderStatus::Pending,
        'total_amount' => 1_000,
        'expires_at' => now(),
    ]);
    Order::query()->create([
        'order_number' => 'ORD-FUTURE',
        'status' => OrderStatus::Pending,
        'total_amount' => 1_000,
        'expires_at' => now()->addSecond(),
    ]);
    Order::query()->create([
        'order_number' => 'ORD-CANCELLED',
        'status' => OrderStatus::Cancelled,
        'total_amount' => 1_000,
        'expires_at' => now()->subSecond(),
    ]);
    Queue::fake([ExpireOrderJob::class]);

    $exitCode = Artisan::call('orders:expire-pending');

    expect($exitCode)->toBe(Command::SUCCESS);
    Queue::assertPushedOnce(ExpireOrderJob::class);
    Queue::assertPushed(
        ExpireOrderJob::class,
        fn (ExpireOrderJob $job) => $job->orderId === $dueOrder->id,
    );
});
