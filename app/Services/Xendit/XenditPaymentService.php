<?php

namespace App\Services\Xendit;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class XenditPaymentService
{
    public function createSession(array $payload): array
    {
        return $this->client()
            ->post('/sessions', $payload)
            ->throw()
            ->json();
    }

    public function refund(Payment $payment): array
    {
        return $this->client()
            ->post('/refunds', [
                'reference_id' => $payment->refundReferenceId(),
                'payment_request_id' => $payment->provider_payment_request_id,
                'currency' => config('payment.currency'),
                'amount' => $payment->amount,
                'reason' => 'CANCELLATION',
            ])
            ->throw()
            ->json();
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth(
            config('services.xendit.secret_key'),
            ''
        )
            ->acceptJson()
            ->baseUrl('https://api.xendit.co');
    }
}
