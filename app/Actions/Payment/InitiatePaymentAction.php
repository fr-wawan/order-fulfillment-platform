<?php

namespace App\Actions\Payment;

use App\Enums\Payment\PaymentSessionStatus;
use App\Exceptions\Payment\PaymentInitiationInProgressException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Xendit\XenditPaymentService;

class InitiatePaymentAction
{
    public function __construct(private CreatePaymentAction $createPaymentAction, private XenditPaymentService $xenditPaymentService) {}

    public function handle(Order $order): Payment
    {
        $payment = $this->createPaymentAction->handle($order);

        if ($payment->session_status === PaymentSessionStatus::Ready) return $payment;

        if (!$payment->wasRecentlyCreated) {
            throw new PaymentInitiationInProgressException();
        }

        $session = $this->xenditPaymentService->createSession([
            'reference_id' => $payment->providerReferenceId(),
            'session_type' => 'PAY',
            'mode' => 'PAYMENT_LINK',
            'amount' => $payment->amount,
            'currency' => config('payment.currency'),
            'country' => config('services.xendit.country'),
            'expires_at' => $payment->order->expires_at->toIso8601String(),
            'success_return_url' => route('orders.show', $order->id),
            'cancel_return_url' => route('orders.show', $order->id),
        ]);

        $payment->update([
            'provider_session_id' => $session['payment_session_id'],
            'checkout_url' => $session['payment_link_url'],
            'session_status' => PaymentSessionStatus::Ready,
        ]);

        return $payment;
    }
}
