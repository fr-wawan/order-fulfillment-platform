<?php

namespace App\Jobs\Payment;

use App\Enums\Payment\PaymentStatus;
use App\Models\Payment;
use App\Services\Xendit\XenditPaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\DB;

#[Tries(5)]
class RefundXenditPaymentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $paymentId) {}

    public function handle(XenditPaymentService $xenditPaymentService): void
    {
        $payment = DB::transaction(function (): ?Payment {
            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($this->paymentId);

            if ($payment->status !== PaymentStatus::RefundQueued) {
                return null;
            }

            $payment->update([
                'status' => PaymentStatus::RefundSubmitting,
            ]);

            return $payment;
        });

        if (! $payment) {
            return;
        }

        try {
            $refund = $xenditPaymentService->refund($payment);
        } catch (ConnectionException) {
            return;
        }

        DB::transaction(function () use ($payment, $refund) {
            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->hasFinalRefundStatus()) {
                return;
            }

            $payment->update([
                'status' => PaymentStatus::RefundPending,
                'provider_refund_id' => $refund['id'],
            ]);
        });
    }
}
