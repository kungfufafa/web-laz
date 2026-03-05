<?php

namespace App\Filament\Resources\DonationPaymentTypes\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DonationPaymentTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label(__('filament.resources.donation_payment_types.fields.label'))
                    ->searchable(),
                TextColumn::make('category.label')
                    ->label(__('filament.resources.donation_payment_types.fields.category'))
                    ->searchable(),
                TextColumn::make('key')
                    ->label(__('filament.resources.donation_payment_types.fields.key'))
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_zakat_calculator')
                    ->label(__('filament.resources.donation_payment_types.fields.is_zakat_calculator'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('filament.resources.donation_payment_types.fields.sort_order'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament.resources.donation_payment_types.fields.is_active'))
                    ->boolean(),
                IconColumn::make('is_locked')
                    ->label(__('filament.resources.donation_payment_types.fields.is_locked'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.donation_payment_types.fields.updated_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('donation_category_id')
                    ->label(__('filament.resources.donation_payment_types.filters.category'))
                    ->relationship('category', 'label')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('filament.resources.donation_payment_types.filters.active_status'))
                    ->trueLabel(__('filament.resources.donation_payment_types.filters.active'))
                    ->falseLabel(__('filament.resources.donation_payment_types.filters.inactive')),
                TernaryFilter::make('is_zakat_calculator')
                    ->label(__('filament.resources.donation_payment_types.filters.zakat_calculator')),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
