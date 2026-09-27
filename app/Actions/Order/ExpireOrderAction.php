<?php

namespace App\Actions\Order;

use App\Enums\Order\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

use function Illuminate\Support\now;

class ExpireOrderAction
{
    public function __construct(private ReleaseOrderReservationsAction $releaseOrderReservations) {}

    public function handle(int $orderId): Order
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status !== OrderStatus::Pending || $order->expires_at > now()) {
                return $order;
            }

            $this->releaseOrderReservations->handle($order);

            $order->update([
                'status' => OrderStatus::Expired,
            ]);

            return $order;
        });
    }
}
