<?php

use App\Models\ProviderCallbackLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prunes logs older than retention period', function () {
    // Create old logs (4 months ago)
    ProviderCallbackLog::factory()
        ->count(3)
        ->create(['created_at' => now()->subMonths(4)]);

    // Create recent logs (1 month ago)
    ProviderCallbackLog::factory()
        ->count(2)
        ->create(['created_at' => now()->subMonth()]);

    // Create current logs
    ProviderCallbackLog::factory()
        ->count(2)
        ->create();

    expect(ProviderCallbackLog::query()->count())->toBe(7);

    $this->artisan('model:prune', [
        '--model' => [ProviderCallbackLog::class],
    ])->assertSuccessful();

    expect(ProviderCallbackLog::query()->count())->toBe(4)
        ->and(ProviderCallbackLog::query()->where('created_at', '<', now()->subMonths(ProviderCallbackLog::RETENTION_MONTHS))->count())->toBe(0);
});

it('keeps logs within retention period', function () {
    // Create logs exactly at boundary (3 months ago - should be kept)
    ProviderCallbackLog::factory()
        ->count(2)
        ->create(['created_at' => now()->subMonths(ProviderCallbackLog::RETENTION_MONTHS)]);

    // Create recent logs
    ProviderCallbackLog::factory()
        ->count(3)
        ->create(['created_at' => now()->subDay()]);

    $this->artisan('model:prune', [
        '--model' => [ProviderCallbackLog::class],
    ])->assertSuccessful();

    expect(ProviderCallbackLog::query()->count())->toBe(5);
});

it('has correct retention constant', function () {
    expect(ProviderCallbackLog::RETENTION_MONTHS)->toBe(3);
});
