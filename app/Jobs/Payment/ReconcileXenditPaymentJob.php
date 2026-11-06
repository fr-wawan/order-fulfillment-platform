<?php

namespace App\Jobs\Payment;

use App\Actions\Payment\ClaimPaymentReconciliationAction;
use App\Actions\Payment\RecordPaymentReconciliationOutcomeAction;
use App\Enums\Payment\PaymentStatus;
use App\Models\Payment;
use App\Services\Xendit\XenditPaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;

class ReconcileXenditPaymentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $paymentId,
        private XenditPaymentService $xenditPaymentService,
        private ClaimPaymentReconciliationAction $claimPaymentReconciliationAction,
        private RecordPaymentReconciliationOutcomeAction $recordPaymentReconciliationOutcomeAction,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): ?Payment
    {
        $payment = DB::transaction(function (): ?Payment {
            $payment = Payment::query()
                ->lockForUpdate()
                ->claimableForReconciliation()
                ->find($this->paymentId);

            if (!$payment) {
                return null;
            }

            $this->claimPaymentReconciliationAction->handle($payment);

            return $payment;
        });

        if (!$payment) {
            return null;
        }

        $session = null;
        $errorType = null;


        try {
            $session = $this->xenditPaymentService->getSession($payment->provider_session_id);
        } catch (ConnectionException) {
            $errorType = 'connection';
        }

        return $payment;
    }
}
