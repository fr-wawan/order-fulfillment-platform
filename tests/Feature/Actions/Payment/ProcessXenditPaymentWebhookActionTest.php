<?php

use App\Actions\Payment\ProcessXenditPaymentWebhookAction;
use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Exceptions\Payment\PaymentMismatchException;
use App\Jobs\Payment\RefundXenditPaymentJob;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\travelTo;

/** @return array{order: Order, payment: Payment} */
function createPaymentForSettlement(
    OrderStatus $orderStatus = OrderStatus::Pending,
    string $expiresAt = '2026-09-27 12:30:00',
): array {
    $order = Order::query()->create([
        'order_number' => 'ORD-XENDIT-'.fake()->unique()->numerify('####'),
        'status' => $orderStatus,
        'total_amount' => 125_000,
        'expires_at' => $expiresAt,
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::Pending,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_session_id' => 'ps-'.$order->id,
    ]);

    return compact('order', 'payment');
}

/** @return array<string, int|string> */
function completedPaymentSessionPayload(Payment $payment): array
{
    return [
        'payment_session_id' => $payment->provider_session_id,
        'payment_id' => 'pi-'.$payment->id,
        'payment_request_id' => 'pr-'.$payment->id,
        'status' => 'COMPLETED',
        'reference_id' => $payment->providerReferenceId(),
        'amount' => $payment->amount,
        'currency' => 'IDR',
    ];
}

it('settles a payment received before the order expires', function () {
    travelTo('2026-09-27 12:00:00');
    config(['payment.currency' => 'IDR']);
    Queue::fake();
    ['order' => $order, 'payment' => $payment] = createPaymentForSettlement();

    app(ProcessXenditPaymentWebhookAction::class)->handle(
        completedPaymentSessionPayload($payment),
    );

    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
    expect($payment->refresh())
        ->status->toBe(PaymentStatus::Succeeded)
        ->provider_payment_id->toBe('pi-'.$payment->id)
        ->provider_payment_request_id->toBe('pr-'.$payment->id)
        ->paid_at->toDateTimeString()->toBe('2026-09-27 12:00:00');
    Queue::assertNothingPushed();
});

it('does not process the same completed payment session twice', function () {
    travelTo('2026-09-27 12:00:00');
    config(['payment.currency' => 'IDR']);
    ['payment' => $payment] = createPaymentForSettlement();
    $payload = completedPaymentSessionPayload($payment);

    app(ProcessXenditPaymentWebhookAction::class)->handle($payload);
    travelTo('2026-09-27 12:01:00');
    app(ProcessXenditPaymentWebhookAction::class)->handle($payload);

    expect($payment->refresh())
        ->status->toBe(PaymentStatus::Succeeded)
        ->paid_at->toDateTimeString()->toBe('2026-09-27 12:00:00');
});

it('queues a refund without changing an expired or cancelled order', function (OrderStatus $status) {
    travelTo('2026-09-27 12:00:00');
    config(['payment.currency' => 'IDR']);
    Queue::fake([RefundXenditPaymentJob::class]);
    ['order' => $order, 'payment' => $payment] = createPaymentForSettlement(
        $status,
    );

    app(ProcessXenditPaymentWebhookAction::class)->handle(
        completedPaymentSessionPayload($payment),
    );

    expect($order->refresh()->status)->toBe($status);
    expect($payment->refresh())->status->toBe(PaymentStatus::RefundQueued);
    Queue::assertPushed(
        RefundXenditPaymentJob::class,
        fn (RefundXenditPaymentJob $job) => $job->paymentId === $payment->id,
    );
})->with([
    'cancelled order' => OrderStatus::Cancelled,
    'expired order' => OrderStatus::Expired,
]);

it('does not queue another refund for a duplicate late payment callback', function () {
    travelTo('2026-09-27 12:00:00');
    config(['payment.currency' => 'IDR']);
    Queue::fake([RefundXenditPaymentJob::class]);
    ['payment' => $payment] = createPaymentForSettlement(OrderStatus::Cancelled);
    $payload = completedPaymentSessionPayload($payment);

    app(ProcessXenditPaymentWebhookAction::class)->handle($payload);
    app(ProcessXenditPaymentWebhookAction::class)->handle($payload);

    expect($payment->refresh()->status)->toBe(PaymentStatus::RefundQueued);
    Queue::assertPushedTimes(RefundXenditPaymentJob::class, 1);
});

it('rejects a completed session whose amount does not match the local payment', function () {
    config(['payment.currency' => 'IDR']);
    ['payment' => $payment] = createPaymentForSettlement();
    $payload = completedPaymentSessionPayload($payment);
    $payload['amount'] = 125_001;

    expect(fn () => app(ProcessXenditPaymentWebhookAction::class)->handle($payload))
        ->toThrow(PaymentMismatchException::class, 'Payment amount does not match.');

    expect($payment->refresh())
        ->status->toBe(PaymentStatus::Pending)
        ->provider_payment_id->toBeNull()
        ->paid_at->toBeNull();
});
