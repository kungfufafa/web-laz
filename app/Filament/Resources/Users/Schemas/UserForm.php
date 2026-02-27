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
                'admin' => __('filament.options.role.admin'),
                'member' => __('filament.options.role.member'),
            ];
        }

        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();

        if ($roles === []) {
            return [
                'admin' => __('filament.options.role.admin'),
                'member' => __('filament.options.role.member'),
            ];
        }

        $formattedRoles = [];

        foreach ($roles as $roleValue => $roleName) {
            if (! is_string($roleValue) || ! is_string($roleName)) {
                continue;
            }

            $translatedRole = __('filament.options.role.'.$roleValue);

            $formattedRoles[$roleValue] = str_starts_with($translatedRole, 'filament.options.role.')
                ? Str::of($roleName)->replace('_', ' ')->headline()->toString()
                : $translatedRole;
        }

        return $formattedRoles;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament.resources.users.sections.account_information'))
                    ->description(__('filament.resources.users.descriptions.account_information'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('filament.resources.users.fields.name'))
                            ->placeholder(__('filament.resources.users.placeholders.name'))
                            ->required(),
                        TextInput::make('email')
                            ->label(__('filament.resources.users.fields.email'))
                            ->email()
                            ->placeholder(__('filament.resources.users.placeholders.email'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label(__('filament.resources.users.fields.password'))
                            ->password()
                            ->placeholder(__('filament.resources.users.placeholders.password'))
                            ->minLength(8)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        Select::make('role')
                            ->label(__('filament.resources.users.fields.role'))
                            ->options(fn (): array => static::roleOptions())
                            ->required()
                            ->default('member'),
                    ]),
                Section::make(__('filament.resources.users.sections.profile'))
                    ->description(__('filament.resources.users.descriptions.profile'))
                    ->columns(2)
                    ->components([
                        TextInput::make('phone')
                            ->label(__('filament.resources.users.fields.phone'))
                            ->tel()
                            ->placeholder(__('filament.resources.users.placeholders.phone'))
                            ->unique(ignoreRecord: true),
                        FileUpload::make('avatar_url')
                            ->label(__('filament.resources.users.fields.avatar'))
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars'),
                    ]),
            ]);
    }
}
