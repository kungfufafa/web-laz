<?php

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('legacy admin role is synced and can access filament panel', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->canAccessPanel(\Mockery::mock(Panel::class)))->toBeTrue();
});

test('legacy member role is synced and denied access to filament panel', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);

    expect($member->hasRole('member'))->toBeTrue();
    expect($member->canAccessPanel(\Mockery::mock(Panel::class)))->toBeFalse();
});

test('updating role keeps spatie role assignments in sync', function (): void {
    $user = User::factory()->create([
        'role' => 'member',
    ]);

    $user->update([
        'role' => 'admin',
    ]);

    $user->refresh();

    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('member'))->toBeFalse();
});

test('user with explicit content permission can access filament panel', function (): void {
    $permission = Permission::query()->firstOrCreate([
        'name' => 'ViewAny:Article',
        'guard_name' => 'web',
    ]);

    $member = User::factory()->create([
        'role' => 'member',
    ]);

    $member->givePermissionTo($permission);

    expect($member->canAccessPanel(\Mockery::mock(Panel::class)))->toBeTrue();
});
