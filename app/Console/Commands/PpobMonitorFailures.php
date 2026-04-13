<?php

namespace App\Console\Commands;

use App\Services\PpobAlertService;
use Illuminate\Console\Command;

class PpobMonitorFailures extends Command
{
    protected $signature = 'ppob:monitor-failures {--minutes=15} {--threshold=3}';

    protected $description = 'Detect repeated PPOB failures and emit alerts.';

    public function handle(PpobAlertService $alertService): int
    {
        $summary = $alertService->monitorRecentFailures(
            max(1, (int) $this->option('minutes')),
            max(1, (int) $this->option('threshold')),
        );

        $this->info(sprintf(
            'PPOB monitor checked %d failed and %d stuck transactions in %d minutes.',
            $summary['failed_count'],
            $summary['stuck_count'],
            $summary['window_minutes'],
        ));

        if ($summary['triggered']) {
            $this->warn('PPOB alert threshold reached.');
        }

        return self::SUCCESS;
    }
}
