<?php

namespace App\Actions\Payment;

use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentStatus;
use App\Exceptions\Payment\PaymentMismatchException;
use App\Exceptions\Payment\UnexpectedPaymentStatusException;
use App\Jobs\Payment\RefundXenditPaymentJob;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ProcessXenditPaymentWebhookAction
{
    public function  __construct(private SettleVerifiedXenditPaymentAction $settleVerifiedXenditPaymentAction) {}

    public function handle(array $data): void
    {
        $payment = Payment::query()
            ->where('provider_session_id', $data['payment_session_id'])
            ->first();

        if (! $payment) {
            return;
        }

        DB::transaction(function () use ($data, $payment) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($payment->order_id);

            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if (! $payment->isPending()) {
                return;
            }

            $this->validatePayment($payment, $data);

            $this->settleVerifiedXenditPaymentAction->handle($order, $payment, $data);
        });
    }

    private function validatePayment(Payment $payment, array $data): void
    {
        if ($data['status'] !== 'COMPLETED') {
            throw new UnexpectedPaymentStatusException($data['status']);
        }

        if ($data['reference_id'] !== $payment->providerReferenceId()) {
            throw new PaymentMismatchException('reference_id');
        }

        if ((int) $data['amount'] !== $payment->amount) {
            throw new PaymentMismatchException('amount');
        }

        if ($data['currency'] !== config('payment.currency')) {
            throw new PaymentMismatchException('currency');
        }
    }
}
