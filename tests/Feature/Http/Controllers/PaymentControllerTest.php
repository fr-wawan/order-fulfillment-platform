<?php

use App\Actions\Payment\InitiatePaymentAction;
use App\Enums\Order\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    $order = Order::query()->create([
        'order_number' => 'ORD-GUEST-PAYMENT',
        'status' => OrderStatus::Pending,
        'total_amount' => 125_000,
        'expires_at' => now()->addMinutes(15),
    ]);

    post(route('orders.payments.store', $order))
        ->assertRedirect(route('login'));
});

it('redirects an inertia payment request to the provider checkout', function () {
    $user = User::factory()->create();
    $order = Order::query()->create([
        'order_number' => 'ORD-CHECKOUT',
        'status' => OrderStatus::Pending,
        'total_amount' => 125_000,
        'expires_at' => now()->addMinutes(15),
    ]);
    $payment = new Payment([
        'checkout_url' => 'https://checkout.xendit.co/test-123',
    ]);
    mock(InitiatePaymentAction::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Order $receivedOrder) => $receivedOrder->is($order))
        ->andReturn($payment);

    actingAs($user)
        ->withHeader('X-Inertia', 'true')
        ->post(route('orders.payments.store', $order))
        ->assertConflict()
        ->assertHeader('X-Inertia-Location', 'https://checkout.xendit.co/test-123');
});
