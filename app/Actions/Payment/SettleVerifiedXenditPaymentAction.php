<?php

namespace  App\Actions\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentStatus;

class SettleVerifiedXenditPaymentAction
{
    public function handle(Order $order, Payment $payment, array $data): Payment
    {
        $payment->provider_payment_id = $data['payment_id'];
        $payment->provider_payment_request_id = $data['payment_request_id'];
        $payment->paid_at = now();

        if ($order->status === OrderStatus::Pending && $order->expires_at->isFuture()) {
            $payment->status = PaymentStatus::Succeeded;
            $order->status = OrderStatus::Paid;

            $payment->save();
            $order->save();

            return $payment;
        }

        $payment->status = PaymentStatus::RefundQueued;
        $payment->save();

        return $payment;
    }
}
