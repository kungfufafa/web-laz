<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.resources.payment_methods.fields.name'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('filament.resources.payment_methods.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.payment_method_type.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'bank' => 'info',
                        'qris' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                IconColumn::make('qris_static_payload')
                    ->label(__('filament.resources.payment_methods.fields.qris_template'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->type === 'qris' && filled($record->qris_static_payload)),
                IconColumn::make('qris_image')
                    ->label(__('filament.resources.payment_methods.fields.qris_image_indicator'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->type === 'qris' && filled($record->qris_image)),
                IconColumn::make('is_active')
                    ->label(__('filament.resources.payment_methods.fields.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('filament.resources.payment_methods.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.payment_methods.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('filament.resources.payment_methods.filters.type'))
                    ->options([
                        'bank' => __('filament.options.payment_method_type.bank'),
                        'qris' => __('filament.options.payment_method_type.qris'),
                        'ewallet' => __('filament.options.payment_method_type.ewallet'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('filament.resources.payment_methods.filters.active_status'))
                    ->trueLabel(__('filament.resources.payment_methods.filters.active'))
                    ->falseLabel(__('filament.resources.payment_methods.filters.inactive')),
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
