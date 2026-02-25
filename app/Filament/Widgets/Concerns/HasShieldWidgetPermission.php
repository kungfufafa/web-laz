<?php

namespace App\Filament\Widgets\Concerns;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;

trait HasShieldWidgetPermission
{
    protected static ?string $shieldWidgetPermissionKey = null;

    public static function canView(): bool
    {
        $user = Filament::auth()?->user();

        if (! $user) {
            return false;
        }

        $permission = static::getShieldWidgetPermission();

        if (! is_string($permission) || $permission === '') {
            return parent::canView();
        }

        return $user->can($permission);
    }

    protected static function getShieldWidgetPermission(): ?string
    {
        if (static::$shieldWidgetPermissionKey === null) {
            $widget = FilamentShield::getWidgets()[static::class] ?? null;
            $permissionKey = is_array($widget) ? array_key_first($widget['permissions'] ?? []) : null;

            static::$shieldWidgetPermissionKey = is_string($permissionKey) ? $permissionKey : '';
        }

        return static::$shieldWidgetPermissionKey === '' ? null : static::$shieldWidgetPermissionKey;
    }
}
