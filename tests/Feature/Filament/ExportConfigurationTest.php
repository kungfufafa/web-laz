<?php

use App\Filament\Exports\ArticleExporter;
use App\Filament\Exports\DonationExporter;
use App\Filament\Exports\MemberPrayerExporter;
use App\Filament\Exports\PaymentMethodExporter;
use App\Filament\Exports\UserExporter;
use App\Filament\Exports\VideoExporter;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Donations\Pages\ListDonations;
use App\Filament\Resources\MemberPrayers\Pages\ListMemberPrayers;
use App\Filament\Resources\PaymentMethods\Pages\ListPaymentMethods;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Videos\Pages\ListVideos;
use App\Models\User;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

test('all admin resources register csv and xlsx export actions', function (string $listPageClass, string $exporterClass): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test($listPageClass)
        ->assertActionExists('export', fn (ExportAction $action): bool => $action->getExporter() === $exporterClass
            && $action->getFormats() === [ExportFormat::Csv, ExportFormat::Xlsx])
        ->assertActionExists('create')
        ->assertTableBulkActionDoesNotExist('export')
        ->assertTableActionDoesNotExist('export');
})->with([
    'donations' => [ListDonations::class, DonationExporter::class],
    'member prayers' => [ListMemberPrayers::class, MemberPrayerExporter::class],
    'users' => [ListUsers::class, UserExporter::class],
    'payment methods' => [ListPaymentMethods::class, PaymentMethodExporter::class],
    'articles' => [ListArticles::class, ArticleExporter::class],
    'videos' => [ListVideos::class, VideoExporter::class],
]);

test('all admin resources register expected table filters', function (string $listPageClass, array $filterNames): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    $testable = Livewire::test($listPageClass);

    foreach ($filterNames as $filterName) {
        $testable->assertTableFilterExists($filterName);
    }
})->with([
    'donations filters' => [ListDonations::class, ['category', 'payment_type', 'status', 'payment_method_id']],
    'member prayers filters' => [ListMemberPrayers::class, ['status', 'is_anonymous']],
    'users filters' => [ListUsers::class, ['role']],
    'payment methods filters' => [ListPaymentMethods::class, ['type', 'is_active']],
    'articles filters' => [ListArticles::class, ['is_published']],
    'videos filters' => [ListVideos::class, ['is_published']],
]);
