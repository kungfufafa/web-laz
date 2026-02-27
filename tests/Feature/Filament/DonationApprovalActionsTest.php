<?php

use App\Filament\Resources\Donations\Pages\ListDonations;
use App\Models\Donation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

test('admin can approve pending donation directly from donations list', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $pendingDonation = Donation::factory()->create([
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListDonations::class)
        ->assertTableActionVisible('approve', $pendingDonation)
        ->callTableAction('approve', $pendingDonation, [
            'admin_note' => 'Disetujui setelah verifikasi transfer.',
        ])
        ->assertHasNoTableActionErrors();

    expect($pendingDonation->refresh()->status)->toBe('verified')
        ->and($pendingDonation->admin_note)->toBe('Disetujui setelah verifikasi transfer.');
});

test('admin can reject pending donation directly from donations list', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $pendingDonation = Donation::factory()->create([
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListDonations::class)
        ->assertTableActionVisible('reject', $pendingDonation)
        ->callTableAction('reject', $pendingDonation, [
            'admin_note' => 'Bukti transfer tidak valid.',
        ])
        ->assertHasNoTableActionErrors();

    expect($pendingDonation->refresh()->status)->toBe('rejected')
        ->and($pendingDonation->admin_note)->toBe('Bukti transfer tidak valid.');
});

test('approve and reject actions are hidden for processed donation statuses', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $verifiedDonation = Donation::factory()->create([
        'status' => 'verified',
    ]);
    $rejectedDonation = Donation::factory()->create([
        'status' => 'rejected',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListDonations::class)
        ->assertTableActionHidden('approve', $verifiedDonation)
        ->assertTableActionHidden('reject', $verifiedDonation)
        ->assertTableActionHidden('approve', $rejectedDonation)
        ->assertTableActionHidden('reject', $rejectedDonation);
});

test('member with view donation permission cannot see approve reject actions', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);
    $pendingDonation = Donation::factory()->create([
        'status' => 'pending',
    ]);

    $viewAnyPermission = Permission::query()->firstOrCreate([
        'name' => 'ViewAny:Donation',
        'guard_name' => 'web',
    ]);
    $viewPermission = Permission::query()->firstOrCreate([
        'name' => 'View:Donation',
        'guard_name' => 'web',
    ]);

    $member->givePermissionTo([$viewAnyPermission, $viewPermission]);

    $this->actingAs($member);

    Livewire::test(ListDonations::class)
        ->assertTableActionHidden('approve', $pendingDonation)
        ->assertTableActionHidden('reject', $pendingDonation);
});

test('member with update donation permission cannot see approve reject actions', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);
    $pendingDonation = Donation::factory()->create([
        'status' => 'pending',
    ]);

    $viewAnyPermission = Permission::query()->firstOrCreate([
        'name' => 'ViewAny:Donation',
        'guard_name' => 'web',
    ]);
    $viewPermission = Permission::query()->firstOrCreate([
        'name' => 'View:Donation',
        'guard_name' => 'web',
    ]);
    $updatePermission = Permission::query()->firstOrCreate([
        'name' => 'Update:Donation',
        'guard_name' => 'web',
    ]);

    $member->givePermissionTo([$viewAnyPermission, $viewPermission, $updatePermission]);

    $this->actingAs($member);

    Livewire::test(ListDonations::class)
        ->assertTableActionHidden('approve', $pendingDonation)
        ->assertTableActionHidden('reject', $pendingDonation);
});

test('member with approve reject donation permission can approve and reject from list', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);
    $pendingDonation = Donation::factory()->create([
        'status' => 'pending',
    ]);
    $otherPendingDonation = Donation::factory()->create([
        'status' => 'pending',
    ]);

    $viewAnyPermission = Permission::query()->firstOrCreate([
        'name' => 'ViewAny:Donation',
        'guard_name' => 'web',
    ]);
    $viewPermission = Permission::query()->firstOrCreate([
        'name' => 'View:Donation',
        'guard_name' => 'web',
    ]);
    $approveRejectPermission = Permission::query()->firstOrCreate([
        'name' => 'ApproveReject:Donation',
        'guard_name' => 'web',
    ]);

    $member->givePermissionTo([$viewAnyPermission, $viewPermission, $approveRejectPermission]);

    $this->actingAs($member);

    Livewire::test(ListDonations::class)
        ->assertTableActionVisible('approve', $pendingDonation)
        ->assertTableActionVisible('reject', $otherPendingDonation)
        ->callTableAction('approve', $pendingDonation, [
            'admin_note' => 'Lolos review.',
        ])
        ->callTableAction('reject', $otherPendingDonation, [
            'admin_note' => 'Tidak sesuai data.',
        ])
        ->assertHasNoTableActionErrors();

    expect($pendingDonation->refresh()->status)->toBe('verified')
        ->and($pendingDonation->admin_note)->toBe('Lolos review.')
        ->and($otherPendingDonation->refresh()->status)->toBe('rejected')
        ->and($otherPendingDonation->admin_note)->toBe('Tidak sesuai data.');
});
