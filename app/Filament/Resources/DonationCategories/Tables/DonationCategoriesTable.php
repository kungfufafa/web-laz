<?php

namespace App\Filament\Resources\DonationCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DonationCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label(__('filament.resources.donation_categories.fields.label'))
                    ->searchable(),
                TextColumn::make('key')
                    ->label(__('filament.resources.donation_categories.fields.key'))
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_types_count')
                    ->label(__('filament.resources.donation_categories.fields.payment_types_count'))
                    ->counts([
                        'paymentTypes' => fn (Builder $query): Builder => $query->where('is_active', true),
                    ]),
                TextColumn::make('sort_order')
                    ->label(__('filament.resources.donation_categories.fields.sort_order'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament.resources.donation_categories.fields.is_active'))
                    ->boolean(),
                IconColumn::make('is_locked')
                    ->label(__('filament.resources.donation_categories.fields.is_locked'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.donation_categories.fields.updated_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament.resources.donation_categories.filters.active_status'))
                    ->trueLabel(__('filament.resources.donation_categories.filters.active'))
                    ->falseLabel(__('filament.resources.donation_categories.filters.inactive')),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
