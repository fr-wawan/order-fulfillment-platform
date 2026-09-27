<?php

use App\Actions\Payment\InitiatePaymentAction;
use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Exceptions\PaymentInitiationInProgressException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\travelTo;

function createOrderForPaymentInitiation(): Order
{
    return Order::query()->create([
        'order_number' => fake()->unique()->numerify('ORD-SESSION-####'),
        'status' => OrderStatus::Pending,
        'total_amount' => 125_000,
        'expires_at' => now()->addMinutes(15),
    ]);
}

it('creates a provider session and stores its checkout details', function () {
    travelTo('2026-09-27 12:00:00');
    config([
        'payment.currency' => 'IDR',
        'services.xendit.country' => 'ID',
        'services.xendit.secret_key' => 'xnd_development_test',
    ]);
    $order = createOrderForPaymentInitiation();
    Http::preventStrayRequests();
    Http::fake([
        'https://api.xendit.co/sessions' => Http::response([
            'payment_session_id' => 'ps-test-123',
            'payment_link_url' => 'https://checkout.xendit.co/test-123',
        ]),
    ]);

    $payment = app(InitiatePaymentAction::class)->handle($order);

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->session_status)->toBe(PaymentSessionStatus::Ready)
        ->and($payment->provider_session_id)->toBe('ps-test-123')
        ->and($payment->checkout_url)->toBe('https://checkout.xendit.co/test-123');
    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.xendit.co/sessions'
        && $request['reference_id'] === "payment-{$payment->id}"
        && $request['amount'] === 125_000
        && $request['currency'] === 'IDR'
        && $request['country'] === 'ID'
        && $request['success_return_url'] === route('orders.show', $order)
        && $request['cancel_return_url'] === route('orders.show', $order));
});

it('reuses a payment session that is already ready', function () {
    travelTo('2026-09-27 12:00:00');
    $order = createOrderForPaymentInitiation();
    $existingPayment = Payment::query()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::Pending,
        'session_status' => PaymentSessionStatus::Ready,
        'amount' => $order->total_amount,
        'provider_session_id' => 'ps-existing',
        'checkout_url' => 'https://checkout.xendit.co/existing',
    ]);
    Http::preventStrayRequests();

    $payment = app(InitiatePaymentAction::class)->handle($order);

    expect($payment->is($existingPayment))->toBeTrue()
        ->and($payment->checkout_url)->toBe('https://checkout.xendit.co/existing');
    Http::assertNothingSent();
});

it('rejects another initiation while a session is being created', function () {
    travelTo('2026-09-27 12:00:00');
    $order = createOrderForPaymentInitiation();
    Payment::query()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::Pending,
        'session_status' => PaymentSessionStatus::Initiating,
        'amount' => $order->total_amount,
    ]);
    Http::preventStrayRequests();

    expect(fn () => app(InitiatePaymentAction::class)->handle($order))
        ->toThrow(PaymentInitiationInProgressException::class);
    Http::assertNothingSent();
});
