<?php

namespace App\Actions\Order;

use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Enums\Order\OrderStatus;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public function handle(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status === OrderStatus::Cancelled) {
                return $order;
            }

            $orderItemIds = $order->items()
                ->pluck('id');

            $reservations = InventoryReservation::query()
                ->whereIn('order_item_id', $orderItemIds)
                ->lockForUpdate()
                ->orderBy('id')
                ->get();

            $inventoryIds = $reservations
                ->pluck('inventory_id')
                ->unique()
                ->sort()
                ->values();

            $inventories = Inventory::query()
                ->whereIn('id', $inventoryIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($reservations as $reservation) {
                if ($reservation->status !== InventoryReservationStatus::Reserved) {
                    continue;
                }

                $inventory = $inventories[$reservation->inventory_id];

                $inventory->decrement(
                    'reserved_quantity',
                    $reservation->quantity
                );

                $reservation->update([
                    'status' => InventoryReservationStatus::Released,
                ]);
            }

            $order->update([
                'status' => OrderStatus::Cancelled,
            ]);

            return $order;
        });
    }
}
