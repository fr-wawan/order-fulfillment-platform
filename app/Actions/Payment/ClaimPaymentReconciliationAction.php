<?php

namespace App\Actions\Payment;

use App\Enums\PaymentReconciliationAttempt\PaymentReconciliationAttemptOutcome;
use App\Models\Payment;
use App\Models\PaymentReconciliationAttempt;
use Illuminate\Support\Str;

class ClaimPaymentReconciliationAction
{
    public function handle(Payment $payment): PaymentReconciliationAttempt
    {
        $now = now();
        $leaseToken = Str::uuid();

        $payment->update([
            'reconciliation_lease_token' => $leaseToken,
            'reconciliation_lease_until' => $now->copy()->addMinutes(2),
        ]);

        $attempt = $payment->reconciliationAttempts()->create([
            'lease_token' => $leaseToken,
            'started_at' => $now,
            'outcome' =>  PaymentReconciliationAttemptOutcome::Started,
        ]);

        return $attempt;
    }
}
