<?php

use App\Models\ProviderCallbackLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('model:prune', ['--model' => [ProviderCallbackLog::class]])
    ->daily()
    ->at('02:00')
    ->runInBackground();

Schedule::command('ppob:reconcile-transactions', [
    '--limit' => (int) config('services.ppob.reconcile_batch_limit', 50),
])
    ->cron(sprintf('*/%d * * * *', max(1, (int) config('services.ppob.reconcile_schedule_minutes', 5))))
    ->withoutOverlapping((int) ceil(max(1, (int) config('services.ppob.job_lock_expire_seconds', 300)) / 60))
    ->runInBackground();

Schedule::command('ppob:monitor-failures', [
    '--minutes' => (int) config('services.ppob.failure_alert_window_minutes', 15),
    '--threshold' => (int) config('services.ppob.failure_alert_threshold', 3),
])
    ->cron(sprintf('*/%d * * * *', max(1, (int) config('services.ppob.monitor_schedule_minutes', 5))))
    ->withoutOverlapping((int) ceil(max(1, (int) config('services.ppob.job_lock_expire_seconds', 300)) / 60))
    ->runInBackground();
