<?php

namespace App\Actions\Order;

use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;

class ReleaseOrderReservationsAction
{
    public function handle(Order $order): void
    {
        $orderItemIds = $order->items()->pluck('id');

        $reservations = InventoryReservation::query()
            ->whereIn('order_item_id', $orderItemIds)
            ->lockForUpdate()
            ->orderBy('id')
            ->get();

        $inventories = Inventory::query()
            ->whereIn('id', $reservations->pluck('inventory_id')->unique())
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
    }
}
