<?php

namespace App\Filament\Resources\PpobProducts\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PpobProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label(__('filament.resources.ppob_products.fields.product_name'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('buyer_sku_code')
                    ->label(__('filament.resources.ppob_products.fields.buyer_sku_code'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('service_type')
                    ->label(__('filament.resources.ppob_products.fields.service_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.ppob_service_type.'.$state))
                    ->color(fn (string $state): string => $state === 'postpaid' ? 'info' : 'warning'),
                TextColumn::make('category')
                    ->label(__('filament.resources.ppob_products.fields.category'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('brand')
                    ->label(__('filament.resources.ppob_products.fields.brand'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label(__('filament.resources.ppob_products.fields.type'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('provider_price')
                    ->label(__('filament.resources.ppob_products.fields.provider_price'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('sell_price')
                    ->label(__('filament.resources.ppob_products.fields.sell_price'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('markup_amount')
                    ->label(__('filament.resources.ppob_products.fields.markup_amount'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('pricingRule.name')
                    ->label(__('filament.resources.ppob_products.fields.pricing_rule'))
                    ->placeholder('-')
                    ->toggleable(),
                IconColumn::make('buyer_product_status')
                    ->label(__('filament.resources.ppob_products.fields.buyer_status'))
                    ->boolean(),
                IconColumn::make('seller_product_status')
                    ->label(__('filament.resources.ppob_products.fields.seller_status'))
                    ->boolean(),
                TextColumn::make('synced_at')
                    ->label(__('filament.resources.ppob_products.fields.synced_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.ppob_products.fields.updated_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('service_type')
                    ->label(__('filament.resources.ppob_products.filters.service_type'))
                    ->options([
                        'prepaid' => __('filament.options.ppob_service_type.prepaid'),
                        'postpaid' => __('filament.options.ppob_service_type.postpaid'),
                    ]),
                SelectFilter::make('category')
                    ->label(__('filament.resources.ppob_products.filters.category'))
                    ->options(fn (): array => \App\Models\PpobProduct::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
                SelectFilter::make('brand')
                    ->label(__('filament.resources.ppob_products.filters.brand'))
                    ->options(fn (): array => \App\Models\PpobProduct::query()
                        ->whereNotNull('brand')
                        ->distinct()
                        ->orderBy('brand')
                        ->pluck('brand', 'brand')
                        ->all()),
                TernaryFilter::make('buyer_product_status')
                    ->label(__('filament.resources.ppob_products.filters.buyer_status')),
                TernaryFilter::make('seller_product_status')
                    ->label(__('filament.resources.ppob_products.filters.seller_status')),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
