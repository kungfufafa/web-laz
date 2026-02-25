<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTable
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

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'member' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(fn (): array => static::roleOptions()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
