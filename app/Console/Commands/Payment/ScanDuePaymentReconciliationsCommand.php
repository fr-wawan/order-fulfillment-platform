<?php

namespace App\Console\Commands\Payment;

use App\Enums\Payment\PaymentSessionStatus;
use App\Enums\Payment\PaymentStatus;
use App\Jobs\Payment\ReconcileXenditPaymentJob;
use App\Models\Payment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

#[Signature('payment:scan-due-reconciliations')]
#[Description('Dispatch jobs for due payment reconciliations')]
class ScanDuePaymentReconciliationsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Payment::query()
            ->claimableForReconciliation()
            ->select('id')
            ->chunkById(100, function (Collection $payments) {
                foreach ($payments as $payment) {
                    ReconcileXenditPaymentJob::dispatch($payment);
                }
            });
    }
}
