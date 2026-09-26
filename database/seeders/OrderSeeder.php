<?php

namespace Database\Seeders;

use App\Actions\Order\CancelOrderAction;
use App\Enums\InventoryReservation\InventoryReservationStatus;
use App\Enums\Order\OrderStatus;
use App\Enums\Sku\SkuStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeSkus = Sku::query()
            ->where('status', SkuStatus::Active)
            ->get();
        $inventoriesBySku = Inventory::query()
            ->get()
            ->groupBy('sku_id');

        foreach (range(1, 75) as $orderIndex) {
            $createdAt = fake()->dateTimeBetween('-3 months', 'now');
            $items = $activeSkus
                ->random(fake()->numberBetween(1, 4))
                ->map(fn (Sku $sku) => [
                    'sku' => $sku,
                    'quantity' => fake()->numberBetween(1, 5),
                ]);

            $order = Order::query()->create([
                'order_number' => sprintf('DEMO-%s-%04d', now()->format('YmdHis'), $orderIndex),
                'status' => OrderStatus::Pending,
                'total_amount' => $items->sum(
                    fn (array $item) => $item['sku']->price * $item['quantity'],
                ),
                'expires_at' => now()->addHours(24),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($items as $item) {
                $sku = $item['sku'];
                $quantity = $item['quantity'];
                $inventory = $inventoriesBySku[$sku->id]->random();

                $orderItem = OrderItem::query()->create([
                    'order_id' => $order->id,
                    'sku_id' => $sku->id,
                    'quantity' => $quantity,
                    'unit_price' => $sku->price,
                ]);

                $inventory->increment('reserved_quantity', $quantity);
                $inventory->reservations()->create([
                    'order_item_id' => $orderItem->id,
                    'quantity' => $quantity,
                    'status' => InventoryReservationStatus::Reserved,
                ]);
            }

            if ($orderIndex % 5 === 0) {
                app(CancelOrderAction::class)->handle($order);
            }
        }
    }
}
