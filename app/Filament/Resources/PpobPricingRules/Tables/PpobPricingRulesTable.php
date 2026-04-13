<?php

namespace App\Filament\Resources\PpobPricingRules\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PpobPricingRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.name'))
                    ->searchable(),
                TextColumn::make('specificity')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.specificity'))
                    ->getStateUsing(fn ($record): int => $record->specificity())
                    ->badge(),
                TextColumn::make('service_type')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.service_type'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __('filament.options.ppob_service_type.'.$state) : 'Global')
                    ->badge(),
                TextColumn::make('brand')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.brand'))
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('buyer_sku_code')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.buyer_sku_code'))
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('markup_type')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.markup_type'))
                    ->formatStateUsing(fn (string $state): string => __('filament.options.ppob_markup_type.'.$state))
                    ->badge(),
                TextColumn::make('markup_value')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.markup_value')),
                TextColumn::make('rounding_unit')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.rounding_unit')),
                TextColumn::make('priority')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.priority'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.is_active'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.ppob_pricing_rules.fields.updated_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('priority')
            ->defaultSort('updated_at', 'desc');
    }
}
