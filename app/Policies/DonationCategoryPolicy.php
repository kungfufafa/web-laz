<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DonationCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DonationCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DonationCategory');
    }

    public function view(AuthUser $authUser, DonationCategory $donationCategory): bool
    {
        return $authUser->can('View:DonationCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DonationCategory');
    }

    public function update(AuthUser $authUser, DonationCategory $donationCategory): bool
    {
        return $authUser->can('Update:DonationCategory');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:DonationCategory');
    }

    public function delete(AuthUser $authUser, DonationCategory $donationCategory): bool
    {
        return $authUser->can('Delete:DonationCategory') && ! $donationCategory->is_locked;
    }

    public function restore(AuthUser $authUser, DonationCategory $donationCategory): bool
    {
        return $authUser->can('Restore:DonationCategory');
    }

    public function forceDelete(AuthUser $authUser, DonationCategory $donationCategory): bool
    {
        return $authUser->can('ForceDelete:DonationCategory') && ! $donationCategory->is_locked;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DonationCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DonationCategory');
    }

    public function replicate(AuthUser $authUser, DonationCategory $donationCategory): bool
    {
        return $authUser->can('Replicate:DonationCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DonationCategory');
    }
}
