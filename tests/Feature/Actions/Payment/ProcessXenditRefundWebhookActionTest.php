<?php

use App\Actions\Payment\ProcessXenditRefundWebhookAction;
use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

/** @return array{payment: Payment, payload: array<string, int|string>} */
function createRefundWebhookPayment(): array
{
    $order = Order::query()->create([
        'order_number' => 'ORD-REFUND-'.fake()->unique()->numerify('####'),
        'status' => OrderStatus::Cancelled,
        'total_amount' => 125_000,
        'expires_at' => now()->subMinute(),
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::RefundPending,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_session_id' => 'ps-refund-'.$order->id,
        'provider_payment_id' => 'pi-refund-'.$order->id,
        'provider_payment_request_id' => 'pr-refund-'.$order->id,
        'paid_at' => now()->subMinute(),
    ]);

    return [
        'payment' => $payment,
        'payload' => [
            'payment_request_id' => $payment->provider_payment_request_id,
            'status' => 'SUCCEEDED',
            'reference_id' => $payment->refundReferenceId(),
            'amount' => $payment->amount,
            'currency' => 'IDR',
        ],
    ];
}

it('marks a refund as refunded after Xendit confirms it', function () {
    config(['payment.currency' => 'IDR']);
    ['payment' => $payment, 'payload' => $payload] = createRefundWebhookPayment();

    app(ProcessXenditRefundWebhookAction::class)->handle($payload);

    expect($payment->refresh()->status)->toBe(PaymentStatus::Refunded);
});
