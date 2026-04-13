<?php

namespace App\Console\Commands;

use App\Services\PpobTransactionService;
use Illuminate\Console\Command;

class PpobReconcileTransactions extends Command
{
    protected $signature = 'ppob:reconcile-transactions {--limit=50}';

    protected $description = 'Reconcile PPOB transactions against the active payment gateway and Digiflazz.';

    public function handle(PpobTransactionService $transactionService): int
    {
        $count = $transactionService->reconcileTransactions(
            max(1, (int) $this->option('limit'))
        );

        $this->info(sprintf('Dispatched reconciliation for %d PPOB transactions.', $count));

        return self::SUCCESS;
    }
}
