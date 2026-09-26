<?php

namespace App\Jobs\Order;

use App\Actions\Order\ExpireOrderAction;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpireOrderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order) {}

    /**
     * Execute the job.
     */
    public function handle(ExpireOrderAction $action): void
    {
        $action->handle($this->order);
    }
}
