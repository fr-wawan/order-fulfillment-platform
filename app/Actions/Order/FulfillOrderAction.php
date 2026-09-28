<?php

namespace App\Actions\Order;

use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Enums\Order\OrderStatus;
use App\Exceptions\InventoryReservation\UnexpectedInventoryReservationStatusException;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class FulfillOrderAction
{
    public function handle(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status !== OrderStatus::Paid) {
                return $order;
            }

            $this->handleFulfilledInventories($order);

            $order->update([
                'status' => OrderStatus::Fulfilled,
            ]);

            return $order;
        });
    }

    private function handleFulfilledInventories(Order $order): void
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
                throw new UnexpectedInventoryReservationStatusException($reservation);
            }

            $inventory = $inventories[$reservation->inventory_id];

            $inventory->decrement(
                'reserved_quantity',
                $reservation->quantity
            );

            $inventory->decrement(
                'quantity',
                $reservation->quantity
            );

            $reservation->update([
                'status' => InventoryReservationStatus::Fulfilled,
            ]);
        }
    }
}
