<?php

use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Jobs\Payment\RefundXenditPaymentJob;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Xendit\XenditPaymentService;

use function Pest\Laravel\mock;

it('requests a refund only while the payment is refund pending', function () {
    $order = Order::query()->create([
        'order_number' => 'ORD-REFUND-JOB',
        'status' => OrderStatus::Cancelled,
        'total_amount' => 125_000,
        'expires_at' => now()->subMinute(),
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::RefundPending,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_payment_request_id' => 'pr-refund-job',
    ]);
    $xenditPaymentService = mock(XenditPaymentService::class);
    $xenditPaymentService
        ->shouldReceive('refund')
        ->once()
        ->withArgs(fn (Payment $receivedPayment) => $receivedPayment->is($payment))
        ->andReturn([]);

    (new RefundXenditPaymentJob($payment->id))->handle($xenditPaymentService);

    expect($payment->refresh()->status)->toBe(PaymentStatus::RefundPending);
});

it('does not request a refund after the payment has been refunded', function () {
    $order = Order::query()->create([
        'order_number' => 'ORD-REFUND-ALREADY-COMPLETE',
        'status' => OrderStatus::Cancelled,
        'total_amount' => 125_000,
        'expires_at' => now()->subMinute(),
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::Refunded,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_payment_request_id' => 'pr-refunded-job',
    ]);
    $xenditPaymentService = mock(XenditPaymentService::class);
    $xenditPaymentService->shouldNotReceive('refund');

    (new RefundXenditPaymentJob($payment->id))->handle($xenditPaymentService);

    expect($payment->refresh()->status)->toBe(PaymentStatus::Refunded);
});
