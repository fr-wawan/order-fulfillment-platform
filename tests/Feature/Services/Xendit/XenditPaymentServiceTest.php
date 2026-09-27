<?php

use App\Services\Xendit\XenditPaymentService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('creates a payment session using Xendit basic authentication', function () {
    config(['services.xendit.secret_key' => 'xnd_development_test']);
    Http::preventStrayRequests();
    Http::fake([
        'https://api.xendit.co/sessions' => Http::response([
            'payment_session_id' => 'ps-test-123',
            'payment_link_url' => 'https://checkout.xendit.co/test-123',
        ]),
    ]);

    $session = app(XenditPaymentService::class)->createSession([
        'reference_id' => 'payment-1',
        'amount' => 125_000,
    ]);

    expect($session)->toMatchArray([
        'payment_session_id' => 'ps-test-123',
        'payment_link_url' => 'https://checkout.xendit.co/test-123',
    ]);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.xendit.co/sessions'
        && $request->hasHeader('Authorization', 'Basic '.base64_encode('xnd_development_test:'))
        && $request['reference_id'] === 'payment-1'
        && $request['amount'] === 125_000);
});
