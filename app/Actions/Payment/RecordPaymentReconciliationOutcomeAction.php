<?php

namespace App\Actions\Payment;

use App\Models\Payment;
use App\Models\PaymentReconciliationAttempt;

class RecordPaymentReconciliationOutcomeAction
{
    public function handle(
        Payment $payment,
        PaymentReconciliationAttempt $attempt,
        ?array $session,
        ?string $errorType = null
    ) {}
}
