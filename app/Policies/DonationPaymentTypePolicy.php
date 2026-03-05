<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DonationPaymentType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DonationPaymentTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DonationPaymentType');
    }

    public function view(AuthUser $authUser, DonationPaymentType $donationPaymentType): bool
    {
        return $authUser->can('View:DonationPaymentType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DonationPaymentType');
    }

    public function update(AuthUser $authUser, DonationPaymentType $donationPaymentType): bool
    {
        return $authUser->can('Update:DonationPaymentType');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:DonationPaymentType');
    }

    public function delete(AuthUser $authUser, DonationPaymentType $donationPaymentType): bool
    {
        return $authUser->can('Delete:DonationPaymentType') && ! $donationPaymentType->is_locked;
    }

    public function restore(AuthUser $authUser, DonationPaymentType $donationPaymentType): bool
    {
        return $authUser->can('Restore:DonationPaymentType');
    }

    public function forceDelete(AuthUser $authUser, DonationPaymentType $donationPaymentType): bool
    {
        return $authUser->can('ForceDelete:DonationPaymentType') && ! $donationPaymentType->is_locked;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DonationPaymentType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DonationPaymentType');
    }

    public function replicate(AuthUser $authUser, DonationPaymentType $donationPaymentType): bool
    {
        return $authUser->can('Replicate:DonationPaymentType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DonationPaymentType');
    }
}
