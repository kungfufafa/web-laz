<?php

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

test('resource labels follow active locale', function (string $locale, array $expected): void {
    app()->setLocale($locale);

    expect(UserResource::getModelLabel())->toBe($expected['model'])
        ->and(UserResource::getNavigationLabel())->toBe($expected['navigation'])
        ->and(UserResource::getNavigationGroup())->toBe($expected['group']);
})->with([
    'indonesian' => [
        'locale' => 'id',
        'expected' => [
            'model' => 'Anggota',
            'navigation' => 'Anggota',
            'group' => 'Pengguna',
        ],
    ],
    'english' => [
        'locale' => 'en',
        'expected' => [
            'model' => 'Member',
            'navigation' => 'Members',
            'group' => 'Users',
        ],
    ],
]);

test('users list labels follow active locale', function (string $locale, array $expected): void {
    app()->setLocale($locale);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertSee($expected['email'])
        ->assertSee($expected['role']);
})->with([
    'indonesian' => [
        'locale' => 'id',
        'expected' => [
            'email' => 'Email',
            'role' => 'Peran',
        ],
    ],
    'english' => [
        'locale' => 'en',
        'expected' => [
            'email' => 'Email Address',
            'role' => 'Role',
        ],
    ],
]);

test('user exporter labels follow active locale', function (string $locale, string $expectedRoleLabel): void {
    app()->setLocale($locale);

    $roleColumn = collect(UserExporter::getColumns())
        ->first(fn ($column) => $column->getName() === 'role');

    expect($roleColumn)->not->toBeNull()
        ->and($roleColumn->getLabel())->toBe($expectedRoleLabel);
})->with([
    'indonesian' => ['locale' => 'id', 'expectedRoleLabel' => 'Peran'],
    'english' => ['locale' => 'en', 'expectedRoleLabel' => 'Role'],
]);
