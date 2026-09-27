<?php

namespace App\Console\Commands;

use App\Jobs\Order\ExpireOrderJob;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('orders:expire-pending')]
#[Description('Dispatch jobs for expired pending orders')]
class ExpirePendingOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Order::query()
            ->dueForExpiration()
            ->select('id')
            ->chunkById(100, function (Collection $orders) {
                foreach ($orders as $order) {
                    ExpireOrderJob::dispatch($order->id);
                }
            });

        return self::SUCCESS;
    }
}
