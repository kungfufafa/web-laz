<?php

namespace App\Console\Commands;

use App\Models\PpobPricingRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PpobHealthCheck extends Command
{
    protected $signature = 'ppob:health-check';

    protected $description = 'Validate PPOB production readiness: config, DB, queue, cache, and pricing rules.';

    public function handle(): int
    {
        $checks = [];
        $paymentGateway = (string) config('services.ppob.payment_gateway', 'midtrans');
        $dispatchMode = (string) config('services.ppob.fulfillment_dispatch', 'queue');
        $queueConnectionName = (string) config('queue.default', 'sync');
        $queueConnection = config("queue.connections.{$queueConnectionName}", []);
        $queueDriver = is_array($queueConnection)
            ? (string) ($queueConnection['driver'] ?? $queueConnectionName)
            : $queueConnectionName;
        $queueTable = is_array($queueConnection)
            ? (string) ($queueConnection['table'] ?? 'jobs')
            : 'jobs';
        $failedDriver = (string) config('queue.failed.driver', 'null');
        $failedJobsTable = (string) config('queue.failed.table', 'failed_jobs');
        $requiresQueuedDispatch = $dispatchMode !== 'sync';

        foreach ([
            'services.digiflazz.username',
            'services.digiflazz.api_key',
            'services.digiflazz.webhook_secret',
        ] as $key) {
            $checks[] = [
                'check' => $key,
                'status' => filled(config($key)) ? 'OK' : 'FAIL',
                'detail' => filled(config($key)) ? 'configured' : 'missing',
            ];
        }

        if ($paymentGateway === 'midtrans') {
            foreach ([
                'services.midtrans.server_key',
                'services.midtrans.client_key',
                'services.midtrans.merchant_id',
            ] as $key) {
                $checks[] = [
                    'check' => $key,
                    'status' => filled(config($key)) ? 'OK' : 'FAIL',
                    'detail' => filled(config($key)) ? 'configured' : 'missing',
                ];
            }

            $isProduction = (bool) config('services.midtrans.is_production');
            $checks[] = [
                'check' => 'midtrans_production_mode',
                'status' => $isProduction ? 'OK' : 'WARN',
                'detail' => $isProduction ? 'enabled' : 'sandbox',
            ];

            $appUrl = (string) config('app.url');
            $isHttps = str_starts_with($appUrl, 'https://');
            $checks[] = [
                'check' => 'app_url_https',
                'status' => $isHttps ? 'OK' : 'WARN',
                'detail' => $isHttps ? 'secured' : 'insecure (callbacks may fail)',
            ];
        } elseif ($paymentGateway === 'tripay') {
            foreach ([
                'services.tripay.base_url',
                'services.tripay.api_key',
                'services.tripay.private_key',
                'services.tripay.merchant_code',
            ] as $key) {
                $checks[] = [
                    'check' => $key,
                    'status' => filled(config($key)) ? 'OK' : 'FAIL',
                    'detail' => filled(config($key)) ? 'configured' : 'missing',
                ];
            }

            $returnUrl = (string) config('services.tripay.return_url');
            $checks[] = [
                'check' => 'services.tripay.return_url',
                'status' => filled($returnUrl) ? 'OK' : 'WARN',
                'detail' => filled($returnUrl) ? 'configured' : 'missing',
            ];

            $appUrl = (string) config('app.url');
            $isHttps = str_starts_with($appUrl, 'https://');
            $checks[] = [
                'check' => 'app_url_https',
                'status' => $isHttps ? 'OK' : 'WARN',
                'detail' => $isHttps ? 'secured' : 'insecure (callbacks may fail)',
            ];
        } else {
            $checks[] = [
                'check' => 'services.ppob.payment_gateway',
                'status' => 'WARN',
                'detail' => sprintf('%s (gateway-specific checks skipped)', $paymentGateway),
            ];
        }

        try {
            DB::connection()->getPdo();
            $checks[] = ['check' => 'database_connection', 'status' => 'OK', 'detail' => 'connected'];
        } catch (\Throwable $exception) {
            $checks[] = ['check' => 'database_connection', 'status' => 'FAIL', 'detail' => $exception->getMessage()];
        }

        if (! $requiresQueuedDispatch) {
            $checks[] = [
                'check' => 'jobs_table',
                'status' => 'OK',
                'detail' => 'not required when PPOB dispatch mode is sync',
            ];
            $checks[] = [
                'check' => 'failed_jobs_table',
                'status' => 'OK',
                'detail' => 'not required when PPOB dispatch mode is sync',
            ];
        } else {
            $checks[] = [
                'check' => 'jobs_table',
                'status' => $queueDriver === 'database'
                    ? (Schema::hasTable($queueTable) ? 'OK' : 'FAIL')
                    : 'OK',
                'detail' => $queueDriver === 'database'
                    ? (Schema::hasTable($queueTable) ? 'present' : sprintf('missing (%s)', $queueTable))
                    : sprintf('not required for %s queue', $queueDriver),
            ];
            $checks[] = [
                'check' => 'failed_jobs_table',
                'status' => in_array($failedDriver, ['database', 'database-uuids'], true)
                    ? (Schema::hasTable($failedJobsTable) ? 'OK' : 'FAIL')
                    : 'OK',
                'detail' => in_array($failedDriver, ['database', 'database-uuids'], true)
                    ? (Schema::hasTable($failedJobsTable) ? 'present' : sprintf('missing (%s)', $failedJobsTable))
                    : sprintf('not required for %s failed-job driver', $failedDriver),
            ];
        }
        $checks[] = [
            'check' => 'ppob_pricing_rules',
            'status' => PpobPricingRule::query()->exists() ? 'OK' : 'WARN',
            'detail' => PpobPricingRule::query()->exists() ? 'seeded' : 'empty',
        ];
        $checks[] = [
            'check' => 'queue_connection',
            'status' => config('queue.default') === 'sync' ? 'WARN' : 'OK',
            'detail' => (string) config('queue.default'),
        ];

        try {
            $lock = Cache::lock('ppob-health-check', 5);
            $acquired = $lock->get();
            if ($acquired) {
                $lock->release();
            }

            $checks[] = ['check' => 'cache_lock', 'status' => $acquired ? 'OK' : 'WARN', 'detail' => $acquired ? 'acquired' : 'not acquired'];
        } catch (\Throwable $exception) {
            $checks[] = ['check' => 'cache_lock', 'status' => 'FAIL', 'detail' => $exception->getMessage()];
        }

        $this->table(['Check', 'Status', 'Detail'], array_map(fn (array $item): array => [
            $item['check'],
            $item['status'],
            $item['detail'],
        ], $checks));

        $hasFailure = collect($checks)->contains(fn (array $item): bool => $item['status'] === 'FAIL');

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}
