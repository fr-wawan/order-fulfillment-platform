<?php

use App\Actions\Payment\CreatePaymentAction;
use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Exceptions\Order\OrderNotPayableException;
use App\Models\Order;
use App\Models\Payment;

use function Pest\Laravel\travelTo;

/** @param array<string, mixed> $attributes */
function createPayableOrder(array $attributes = []): Order
{
    return Order::query()->create(array_merge([
        'order_number' => fake()->unique()->numerify('ORD-PAY-####'),
        'status' => OrderStatus::Pending,
        'total_amount' => 125_000,
        'expires_at' => now()->addMinutes(5),
    ], $attributes));
}

it('creates one pending payment and extends a nearly expired order', function () {
    travelTo('2026-09-27 12:00:00');
    $order = createPayableOrder();

    $payment = app(CreatePaymentAction::class)->handle($order);

    expect($payment->order_id)->toBe($order->id)
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->session_status)->toBe(PaymentSessionStatus::Initiating)
        ->and($payment->amount)->toBe(125_000);
    expect((string) $order->refresh()->getRawOriginal('expires_at'))
        ->toBe('2026-09-27 12:10:00');
});

it('keeps an order expiration that is already beyond the minimum window', function () {
    travelTo('2026-09-27 12:00:00');
    $order = createPayableOrder([
        'expires_at' => '2026-09-27 12:30:00',
    ]);

    app(CreatePaymentAction::class)->handle($order);

    expect((string) $order->refresh()->getRawOriginal('expires_at'))
        ->toBe('2026-09-27 12:30:00');
});

it('reuses the existing payment when initiated more than once', function () {
    travelTo('2026-09-27 12:00:00');
    $order = createPayableOrder();

    $firstPayment = app(CreatePaymentAction::class)->handle($order);
    $secondPayment = app(CreatePaymentAction::class)->handle($order);

    expect($secondPayment->is($firstPayment))->toBeTrue();
    expect(Payment::query()->count())->toBe(1);
    expect((string) $order->refresh()->getRawOriginal('expires_at'))
        ->toBe('2026-09-27 12:10:00');
});

it('rejects orders that are not payable', function (array $attributes) {
    travelTo('2026-09-27 12:00:00');
    $order = createPayableOrder($attributes);

    expect(fn () => app(CreatePaymentAction::class)->handle($order))
        ->toThrow(OrderNotPayableException::class, 'Order is not payable.');
})->with([
    'cancelled order' => [['status' => OrderStatus::Cancelled]],
    'expired pending order' => [['expires_at' => '2026-09-27 12:00:00']],
]);
