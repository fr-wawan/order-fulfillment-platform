<?php

namespace App\Services\Xendit;

use Illuminate\Support\Facades\Http;

class XenditPaymentService
{
    public function createSession(array $payload): array
    {
        return Http::withBasicAuth(
            config('services.xendit.secret_key'),
            ''
        )
            ->acceptJson()
            ->post('https://api.xendit.co/sessions', $payload)
            ->throw()
            ->json();
    }
}
