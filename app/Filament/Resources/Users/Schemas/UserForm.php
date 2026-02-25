<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserForm
{
    /**
     * @return array<string, string>
     */
    protected static function roleOptions(): array
    {
        $rolesTable = config('permission.table_names.roles');

        if (! is_string($rolesTable) || ! SchemaFacade::hasTable($rolesTable)) {
            return [
                'admin' => 'Admin',
                'member' => 'Member',
            ];
        }

        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();

        if ($roles === []) {
            return [
                'admin' => 'Admin',
                'member' => 'Member',
            ];
        }

        $formattedRoles = [];

        foreach ($roles as $roleValue => $roleName) {
            if (! is_string($roleValue) || ! is_string($roleName)) {
                continue;
            }

            $formattedRoles[$roleValue] = Str::of($roleName)->replace('_', ' ')->headline()->toString();
        }

        return $formattedRoles;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Akun')
                    ->description('Data utama akun pengguna untuk autentikasi dan otorisasi.')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Contoh: Ahmad Fauzi')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('contoh@email.com')
                            ->required(),
                        TextInput::make('password')
                            ->label('Password Awal')
                            ->password()
                            ->placeholder('Minimal 8 karakter')
                            ->required()
                            ->visibleOn('create'),
                        Select::make('role')
                            ->label('Peran')
                            ->options(fn (): array => static::roleOptions())
                            ->required()
                            ->default('member'),
                    ]),
                Section::make('Profil')
                    ->description('Informasi tambahan yang ditampilkan di aplikasi.')
                    ->columns(2)
                    ->components([
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->placeholder('08xxxxxxxxxx'),
                        FileUpload::make('avatar_url')
                            ->label('Foto Profil')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars'),
                    ]),
            ]);
    }
}
