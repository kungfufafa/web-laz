<?php

use App\Filament\Resources\Donations\Pages\CreateDonation;
use App\Filament\Resources\Donations\Pages\ListDonations;
use App\Models\Donation;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

test('donations list defaults to pending status records for quick approval review', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $pendingDonation = Donation::factory()->create([
        'status' => 'pending',
    ]);
    $verifiedDonation = Donation::factory()->create([
        'status' => 'verified',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListDonations::class)
        ->assertCanSeeTableRecords([$pendingDonation])
        ->assertCanNotSeeTableRecords([$verifiedDonation]);
});

test('donations list shows proof and internal note columns for approval context', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $donationWithProof = Donation::factory()->create([
        'status' => 'pending',
        'proof_image' => 'proofs/sample-transfer.jpg',
        'admin_note' => 'Menunggu review bendahara.',
    ]);
    $donationWithoutProof = Donation::factory()->create([
        'status' => 'pending',
        'proof_image' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(ListDonations::class)
        ->assertTableColumnExists('proof_image')
        ->assertTableColumnExists('admin_note')
        ->assertTableColumnFormattedStateSet('proof_image', __('filament.resources.donations.columns.view_proof'), $donationWithProof)
        ->assertTableColumnFormattedStateSet('proof_image', __('filament.resources.donations.columns.no_proof'), $donationWithoutProof)
        ->assertTableActionVisible('viewProof', $donationWithProof)
        ->assertTableActionHidden('viewProof', $donationWithoutProof)
        ->assertTableColumnStateSet('admin_note', 'Menunggu review bendahara.', $donationWithProof);
});

test('proof image is opened using built-in modal action instead of a new tab url', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $donationWithProof = Donation::factory()->create([
        'status' => 'pending',
        'proof_image' => 'proofs/sample-transfer.jpg',
    ]);

    $this->actingAs($admin);

    $testable = Livewire::test(ListDonations::class)
        ->mountTableAction('viewProof', $donationWithProof)
        ->assertSet('mountedActions.0.name', 'viewProof');

    $viewProofAction = $testable->instance()->getTable()->getAction('viewProof');

    expect($viewProofAction)->not->toBeNull();

    $viewProofAction->record($donationWithProof);

    expect($viewProofAction->getUrl())->toBeNull()
        ->and($viewProofAction->shouldOpenUrlInNewTab())->toBeFalse();
});

test('member donor name and created at columns are hidden by default but remain toggleable', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    Donation::factory()->create([
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    $testable = Livewire::test(ListDonations::class)
        ->assertTableColumnExists('user.name')
        ->assertTableColumnExists('donor_name')
        ->assertTableColumnExists('created_at');

    $memberColumn = $testable->instance()->getTable()->getColumn('user.name');
    $donorColumn = $testable->instance()->getTable()->getColumn('donor_name');
    $createdAtColumn = $testable->instance()->getTable()->getColumn('created_at');

    expect($memberColumn->isToggleable())->toBeTrue()
        ->and($memberColumn->isToggledHiddenByDefault())->toBeTrue()
        ->and($memberColumn->isToggledHidden())->toBeTrue()
        ->and($donorColumn->isToggleable())->toBeTrue()
        ->and($donorColumn->isToggledHiddenByDefault())->toBeTrue()
        ->and($donorColumn->isToggledHidden())->toBeTrue()
        ->and($createdAtColumn->isToggleable())->toBeTrue()
        ->and($createdAtColumn->isToggledHiddenByDefault())->toBeTrue()
        ->and($createdAtColumn->isToggledHidden())->toBeTrue();
});

test('create donation form stores proof image on private proofs directory', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test(CreateDonation::class)
        ->assertFormFieldExists('proof_image', function ($field): bool {
            if (! $field instanceof FileUpload) {
                return false;
            }

            return $field->getDiskName() === 'local'
                && $field->getDirectory() === 'proofs'
                && $field->getVisibility() === 'private';
        });
});
