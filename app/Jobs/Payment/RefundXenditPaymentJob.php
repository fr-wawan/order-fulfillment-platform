<?php

namespace App\Jobs\Payment;

use App\Enums\Payment\PaymentStatus;
use App\Models\Payment;
use App\Services\Xendit\XenditPaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Tries;

#[Tries(5)]
class RefundXenditPaymentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $paymentId) {}

    public function handle(XenditPaymentService $xenditPaymentService): void
    {
        $payment = Payment::findOrFail($this->paymentId);

        if ($payment->status !== PaymentStatus::RefundPending) return;

        $xenditPaymentService->refund($payment);
    }
}
