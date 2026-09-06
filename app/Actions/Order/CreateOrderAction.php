<?php

namespace App\Actions\Order;

use App\Enums\Order\OrderStatus;
use App\Models\Order;
use App\Models\Sku;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function handle(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $items = $this->normalizeItems($data['items']);

            $skuIds = $items->map(fn (array $item) => $item['sku_id']);

            $skus = Sku::whereIn('id', $skuIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'status' => OrderStatus::Pending,
                'total_amount' => $this->calculateTotalAmount($items, $skus),
            ]);

            $this->createOrderItems($order, $items, $skus);

            return $order;
        });
    }

    private function normalizeItems(array $items): Collection
    {
        return collect($items)
            ->groupBy(fn (array $item) => (int) $item['sku_id'])
            ->map(fn ($items, $skuId) => [
                'sku_id' => (int) $skuId,
                'quantity' => $items->sum(
                    fn (array $item) => (int) $item['quantity']
                ),
            ])
            ->values();
    }

    private function generateOrderNumber(): string
    {
        return strtoupper('ORD-'.Str::random(8));
    }

    private function calculateTotalAmount(Collection $items, Collection $skus): int
    {
        return $items->sum(function (array $item) use ($skus) {
            $sku = $skus[$item['sku_id']];

            return $sku->price * $item['quantity'];
        });
    }

    private function createOrderItems(Order $order, Collection $items, Collection $skus): void
    {
        $items->each(function (array $item) use ($order, $skus) {
            $sku = $skus[$item['sku_id']];

            $order->items()->create([
                'sku_id' => $sku->id,
                'quantity' => $item['quantity'],
                'unit_price' => $sku->price,
            ]);
        });
    }
}
