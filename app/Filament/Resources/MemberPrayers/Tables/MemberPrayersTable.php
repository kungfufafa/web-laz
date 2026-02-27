<?php

namespace App\Filament\Resources\MemberPrayers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MemberPrayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('filament.resources.member_prayers.fields.user'))
                    ->searchable(),
                TextColumn::make('content')
                    ->label(__('filament.resources.member_prayers.fields.content'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('filament.resources.member_prayers.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.member_prayer_status.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'hidden' => 'gray',
                        default => 'warning',
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('filament.resources.member_prayers.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.member_prayers.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.resources.member_prayers.filters.status'))
                    ->options([
                        'published' => __('filament.options.member_prayer_status.published'),
                        'hidden' => __('filament.options.member_prayer_status.hidden'),
                    ]),
                TernaryFilter::make('is_anonymous')
                    ->label(__('filament.resources.member_prayers.filters.anonymous'))
                    ->trueLabel(__('filament.common.anonymous'))
                    ->falseLabel(__('filament.resources.member_prayers.filters.not_anonymous')),
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
