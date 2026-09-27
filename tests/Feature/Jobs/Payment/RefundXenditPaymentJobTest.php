<?php

use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Jobs\Payment\RefundXenditPaymentJob;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Xendit\XenditPaymentService;

use function Pest\Laravel\mock;

it('submits a queued refund and records the provider refund id', function () {
    $order = Order::query()->create([
        'order_number' => 'ORD-REFUND-JOB',
        'status' => OrderStatus::Cancelled,
        'total_amount' => 125_000,
        'expires_at' => now()->subMinute(),
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::RefundQueued,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_payment_request_id' => 'pr-refund-job',
    ]);
    $xenditPaymentService = mock(XenditPaymentService::class);
    $xenditPaymentService
        ->shouldReceive('refund')
        ->once()
        ->withArgs(fn (Payment $receivedPayment) => $receivedPayment->is($payment))
        ->andReturn(['id' => 'refund-123']);

    (new RefundXenditPaymentJob($payment->id))->handle($xenditPaymentService);

    expect($payment->refresh())
        ->status->toBe(PaymentStatus::RefundPending)
        ->provider_refund_id->toBe('refund-123');
});

it('does not overwrite a refund finalized while the provider request is in progress', function () {
    $order = Order::query()->create([
        'order_number' => 'ORD-REFUND-JOB-WEBHOOK-RACE',
        'status' => OrderStatus::Cancelled,
        'total_amount' => 125_000,
        'expires_at' => now()->subMinute(),
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::RefundQueued,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_payment_request_id' => 'pr-refund-job-webhook-race',
    ]);
    $xenditPaymentService = mock(XenditPaymentService::class);
    $xenditPaymentService
        ->shouldReceive('refund')
        ->once()
        ->andReturnUsing(function (Payment $receivedPayment): array {
            $receivedPayment->update([
                'status' => PaymentStatus::Refunded,
                'provider_refund_id' => 'refund-webhook',
            ]);

            return ['id' => 'refund-job'];
        });

    (new RefundXenditPaymentJob($payment->id))->handle($xenditPaymentService);

    expect($payment->refresh())
        ->status->toBe(PaymentStatus::Refunded)
        ->provider_refund_id->toBe('refund-webhook');
});

it('does not submit a refund unless it is queued', function (PaymentStatus $status) {
    $order = Order::query()->create([
        'order_number' => 'ORD-REFUND-ALREADY-COMPLETE',
        'status' => OrderStatus::Cancelled,
        'total_amount' => 125_000,
        'expires_at' => now()->subMinute(),
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => $status,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_payment_request_id' => 'pr-refunded-job',
    ]);
    $xenditPaymentService = mock(XenditPaymentService::class);
    $xenditPaymentService->shouldNotReceive('refund');

    (new RefundXenditPaymentJob($payment->id))->handle($xenditPaymentService);

    expect($payment->refresh()->status)->toBe($status);
})->with([
    'submitting refund' => PaymentStatus::RefundSubmitting,
    'pending provider confirmation' => PaymentStatus::RefundPending,
    'refunded payment' => PaymentStatus::Refunded,
    'failed refund' => PaymentStatus::RefundFailed,
]);
