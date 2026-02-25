<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected string $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $user): void {
            if (! $user->wasRecentlyCreated && ! $user->wasChanged('role')) {
                return;
            }

            $user->syncLegacyRoleIntoPermissions();
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $superAdminRole = config('filament-shield.super_admin.name', 'admin');
        $panelUserRole = config('filament-shield.panel_user.name', 'panel_user');

        if ($this->role === 'admin' || $this->hasAnyRole([$superAdminRole, $panelUserRole])) {
            return true;
        }

        return $this->getAllPermissions()->isNotEmpty();
    }

    public function syncLegacyRoleIntoPermissions(): void
    {
        $rolesTable = config('permission.table_names.roles');
        $modelHasRolesTable = config('permission.table_names.model_has_roles');

        if (
            ! is_string($rolesTable)
            || ! is_string($modelHasRolesTable)
            || ! Schema::hasTable($rolesTable)
            || ! Schema::hasTable($modelHasRolesTable)
        ) {
            return;
        }

        if (! is_string($this->role) || $this->role === '') {
            $this->syncRoles([]);

            return;
        }

        $guardName = config('auth.defaults.guard', 'web');

        Role::query()->firstOrCreate([
            'name' => $this->role,
            'guard_name' => is_string($guardName) ? $guardName : 'web',
        ]);

        $this->syncRoles([$this->role]);
    }

    public function setPhoneAttribute(?string $phone): void
    {
        if (! is_string($phone) || trim($phone) === '') {
            $this->attributes['phone'] = null;

            return;
        }

        $normalizedPhone = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($normalizedPhone, '62')) {
            $this->attributes['phone'] = '0' . substr($normalizedPhone, 2);

            return;
        }

        if (str_starts_with($normalizedPhone, '8')) {
            $this->attributes['phone'] = '0' . $normalizedPhone;

            return;
        }

        $this->attributes['phone'] = $normalizedPhone;
    }

    public function donations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
