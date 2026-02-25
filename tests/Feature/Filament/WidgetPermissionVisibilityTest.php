<?php

use App\Filament\Widgets\ContentStatsWidget;
use App\Filament\Widgets\DonationCategoryChartWidget;
use App\Filament\Widgets\DonationChartWidget;
use App\Filament\Widgets\DonationStatsWidget;
use App\Filament\Widgets\RecentDonationsWidget;
use App\Filament\Widgets\ZakatStatsWidget;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

test('dashboard widgets require matching shield permission', function (string $widgetClass): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);

    $this->actingAs($member);

    expect($widgetClass::canView())->toBeFalse();

    $permission = Permission::query()->firstOrCreate([
        'name' => 'View:' . class_basename($widgetClass),
        'guard_name' => 'web',
    ]);

    $member->givePermissionTo($permission);

    expect($widgetClass::canView())->toBeTrue();
})->with([
    DonationStatsWidget::class,
    ContentStatsWidget::class,
    RecentDonationsWidget::class,
    DonationChartWidget::class,
    DonationCategoryChartWidget::class,
    ZakatStatsWidget::class,
]);
