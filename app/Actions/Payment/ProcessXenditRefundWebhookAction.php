<?php

namespace App\Actions\Payment;

use App\Enums\Payment\PaymentStatus;
use App\Exceptions\Payment\PaymentMismatchException;
use App\Exceptions\Payment\UnexpectedPaymentStatusException;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ProcessXenditRefundWebhookAction
{
    public function handle(array $data): void
    {
        $payment = Payment::query()
            ->where('provider_payment_request_id', $data['payment_request_id'])
            ->first();

        if (! $payment) {
            return;
        }

        DB::transaction(function () use ($data, $payment) {
            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->hasFinalRefundStatus()) {
                return;
            }

            if (! in_array($payment->status, [
                PaymentStatus::RefundSubmitting,
                PaymentStatus::RefundPending,
            ], true)) {
                throw new UnexpectedPaymentStatusException(
                    $payment->status,
                );
            }

            $this->validateRefund($payment, $data);

            $payment->update([
                'status' => match ($data['status']) {
                    'SUCCEEDED' => PaymentStatus::Refunded,
                    'FAILED' => PaymentStatus::RefundFailed,
                    default => throw new UnexpectedPaymentStatusException($data['status']),
                },
                'provider_refund_id' => $data['id'],
            ]);
        });
    }

    private function validateRefund(Payment $payment, array $data): void
    {
        if ($data['reference_id'] !== $payment->refundReferenceId()) {
            throw new PaymentMismatchException('reference_id');
        }

        if ((int) $data['amount'] !== $payment->amount) {
            throw new PaymentMismatchException('amount');
        }

        if ($data['currency'] !== config('payment.currency')) {
            throw new PaymentMismatchException('amount');
        }
    }
}
