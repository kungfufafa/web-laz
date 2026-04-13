<?php

namespace App\Console\Commands;

use App\Services\PpobCatalogService;
use Illuminate\Console\Command;

class PpobSyncDigiflazzCatalog extends Command
{
    protected $signature = 'ppob:sync-digiflazz-catalog {service=all : prepaid, postpaid, or all}';

    protected $description = 'Synchronize PPOB product catalog from Digiflazz.';

    public function handle(PpobCatalogService $catalogService): int
    {
        $service = strtolower((string) $this->argument('service'));
        $targets = match ($service) {
            'prepaid' => ['prepaid'],
            'postpaid' => ['postpaid'],
            default => ['prepaid', 'postpaid'],
        };

        $total = 0;

        foreach ($targets as $target) {
            $count = $catalogService->syncFromDigiflazz($target);
            $total += $count;
            $this->info(sprintf('Synced %d %s products.', $count, $target));
        }

        $this->info(sprintf('Finished syncing %d products.', $total));

        return self::SUCCESS;
    }
}
