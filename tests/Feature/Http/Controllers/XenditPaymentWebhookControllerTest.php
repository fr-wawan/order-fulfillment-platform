<?php

use App\Actions\Payment\ProcessXenditPaymentWebhookAction;
use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

use function Pest\Laravel\mock;

it('returns 401 and leaves the payment unchanged when the callback token is invalid', function () {
    config(['services.xendit.webhook_token' => 'expected-token']);
    $order = Order::query()->create([
        'order_number' => 'ORD-WEBHOOK-UNTRUSTED',
        'status' => OrderStatus::Pending,
        'total_amount' => 125_000,
        'expires_at' => now()->addMinutes(15),
    ]);
    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::Pending,
        'session_status' => PaymentSessionStatus::Ready,
        'provider_session_id' => 'ps-untrusted',
    ]);

    $this->postJson(route('webhooks.xendit.payment'), [
        'event' => 'payment_session.completed',
        'data' => ['payment_session_id' => $payment->provider_session_id],
    ])->assertUnauthorized();

    expect($payment->refresh())
        ->status->toBe(PaymentStatus::Pending)
        ->provider_payment_id->toBeNull();
});

it('returns 401 when the webhook secret is not configured', function () {
    config(['services.xendit.webhook_token' => null]);

    $this->postJson(route('webhooks.xendit.payment'), [
        'event' => 'unsupported',
    ])->assertUnauthorized();
});

it('acknowledges a trusted completed-payment callback', function () {
    config(['services.xendit.webhook_token' => 'expected-token']);
    $payload = ['payment_session_id' => 'ps-trusted'];
    mock(ProcessXenditPaymentWebhookAction::class)
        ->shouldReceive('handle')
        ->once()
        ->with($payload);

    $this->withHeader('X-Callback-Token', 'expected-token')
        ->postJson(route('webhooks.xendit.payment'), [
            'event' => 'payment_session.completed',
            'data' => $payload,
        ])
        ->assertNoContent();

});
