<?php

namespace App\Jobs;

use App\Services\PpobTransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ProcessPpobFulfillment implements ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public function __construct(
        public string $transactionUuid,
    ) {
        $this->tries = max(1, (int) config('services.ppob.job_tries', 5));
        $this->timeout = max(1, (int) config('services.ppob.job_timeout_seconds', 120));
        $this->queue = 'ppob';
    }

    public function handle(PpobTransactionService $transactionService): void
    {
        $transactionService->fulfillTransaction($this->transactionUuid);
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("ppob-transaction:{$this->transactionUuid}"))
                ->shared()
                ->releaseAfter(max(1, (int) config('services.ppob.job_lock_release_seconds', 30)))
                ->expireAfter(max(1, (int) config('services.ppob.job_lock_expire_seconds', 300))),
        ];
    }

    public function backoff(): array
    {
        return [max(1, (int) config('services.ppob.job_backoff_seconds', 30))];
    }
}
