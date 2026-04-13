<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PpobProduct;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PpobProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PpobProduct');
    }

    public function view(AuthUser $authUser, PpobProduct $ppobProduct): bool
    {
        return $authUser->can('View:PpobProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PpobProduct');
    }

    public function update(AuthUser $authUser, PpobProduct $ppobProduct): bool
    {
        return $authUser->can('Update:PpobProduct');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:PpobProduct');
    }

    public function delete(AuthUser $authUser, PpobProduct $ppobProduct): bool
    {
        return $authUser->can('Delete:PpobProduct');
    }

    public function restore(AuthUser $authUser, PpobProduct $ppobProduct): bool
    {
        return $authUser->can('Restore:PpobProduct');
    }

    public function forceDelete(AuthUser $authUser, PpobProduct $ppobProduct): bool
    {
        return $authUser->can('ForceDelete:PpobProduct');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PpobProduct');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PpobProduct');
    }

    public function replicate(AuthUser $authUser, PpobProduct $ppobProduct): bool
    {
        return $authUser->can('Replicate:PpobProduct');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PpobProduct');
    }
}
