<?php

namespace App\Actions\Payment;

use App\Enums\Order\OrderStatus;
use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Exceptions\OrderNotPayableException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CreatePaymentAction
{
    public function handle(Order $order): Payment
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status !== OrderStatus::Pending || $order->expires_at <= now()) {
                throw new OrderNotPayableException;
            }

            $payment = $order->payment()->firstOrCreate([], [
                'status' => PaymentStatus::Pending,
                'session_status' => PaymentSessionStatus::Initiating,
                'amount' => $order->total_amount,
            ]);

            $extensionMinutes = config('payment.expiration_extension_minutes');
            $now = now();
            $minimumExpiresAt = $now->copy()->addMinutes($extensionMinutes);

            if ($payment->wasRecentlyCreated && $order->expires_at <= $minimumExpiresAt) {
                $order->update([
                    'expires_at' => $minimumExpiresAt,
                ]);
            }

            return $payment;
        });
    }
}
