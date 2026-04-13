<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PpobPricingRule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PpobPricingRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PpobPricingRule');
    }

    public function view(AuthUser $authUser, PpobPricingRule $ppobPricingRule): bool
    {
        return $authUser->can('View:PpobPricingRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PpobPricingRule');
    }

    public function update(AuthUser $authUser, PpobPricingRule $ppobPricingRule): bool
    {
        return $authUser->can('Update:PpobPricingRule');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:PpobPricingRule');
    }

    public function delete(AuthUser $authUser, PpobPricingRule $ppobPricingRule): bool
    {
        return $authUser->can('Delete:PpobPricingRule');
    }

    public function restore(AuthUser $authUser, PpobPricingRule $ppobPricingRule): bool
    {
        return $authUser->can('Restore:PpobPricingRule');
    }

    public function forceDelete(AuthUser $authUser, PpobPricingRule $ppobPricingRule): bool
    {
        return $authUser->can('ForceDelete:PpobPricingRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PpobPricingRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PpobPricingRule');
    }

    public function replicate(AuthUser $authUser, PpobPricingRule $ppobPricingRule): bool
    {
        return $authUser->can('Replicate:PpobPricingRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PpobPricingRule');
    }
}
