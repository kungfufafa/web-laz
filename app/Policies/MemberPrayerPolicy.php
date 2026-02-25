<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MemberPrayer;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPrayerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MemberPrayer');
    }

    public function view(AuthUser $authUser, MemberPrayer $memberPrayer): bool
    {
        return $authUser->can('View:MemberPrayer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MemberPrayer');
    }

    public function update(AuthUser $authUser, MemberPrayer $memberPrayer): bool
    {
        return $authUser->can('Update:MemberPrayer');
    }

    public function delete(AuthUser $authUser, MemberPrayer $memberPrayer): bool
    {
        return $authUser->can('Delete:MemberPrayer');
    }

    public function restore(AuthUser $authUser, MemberPrayer $memberPrayer): bool
    {
        return $authUser->can('Restore:MemberPrayer');
    }

    public function forceDelete(AuthUser $authUser, MemberPrayer $memberPrayer): bool
    {
        return $authUser->can('ForceDelete:MemberPrayer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MemberPrayer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MemberPrayer');
    }

    public function replicate(AuthUser $authUser, MemberPrayer $memberPrayer): bool
    {
        return $authUser->can('Replicate:MemberPrayer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MemberPrayer');
    }

}