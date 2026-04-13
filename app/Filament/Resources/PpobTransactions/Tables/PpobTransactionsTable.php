<?php

namespace App\Filament\Resources\PpobTransactions\Tables;

use App\Models\PpobTransaction;
use App\Services\PpobTransactionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PpobTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label(__('filament.resources.ppob_transactions.fields.product_name'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('customer_no')
                    ->label(__('filament.resources.ppob_transactions.fields.customer_no'))
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label(__('filament.resources.ppob_transactions.fields.customer_name'))
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('service_type')
                    ->label(__('filament.resources.ppob_transactions.fields.service_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.ppob_service_type.'.$state))
                    ->color(fn (string $state): string => $state === 'postpaid' ? 'info' : 'warning'),
                TextColumn::make('provider_price')
                    ->label(__('filament.resources.ppob_transactions.fields.provider_price'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('markup_amount')
                    ->label(__('filament.resources.ppob_transactions.fields.markup_amount'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('filament.resources.ppob_transactions.fields.total_amount'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('pricingRule.name')
                    ->label(__('filament.resources.ppob_transactions.fields.pricing_rule'))
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('payment_status')
                    ->label(__('filament.resources.ppob_transactions.fields.payment_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.ppob_payment_status.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'expired' => 'warning',
                        'reversed' => 'danger',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('fulfillment_status')
                    ->label(__('filament.resources.ppob_transactions.fields.fulfillment_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.ppob_fulfillment_status.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'succeeded' => 'success',
                        'manual_review' => 'warning',
                        'failed' => 'danger',
                        'processing' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('payment_channel_name')
                    ->label(__('filament.resources.ppob_transactions.fields.payment_channel'))
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('payment_reference')
                    ->label(__('filament.resources.ppob_transactions.fields.payment_reference'))
                    ->state(fn (PpobTransaction $record): ?string => $record->midtrans_transaction_id
                        ?? $record->midtrans_order_id
                        ?? $record->payment_gateway_reference)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('digiflazz_ref_id')
                    ->label(__('filament.resources.ppob_transactions.fields.digiflazz_ref_id'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('digiflazz_sn')
                    ->label(__('filament.resources.ppob_transactions.fields.digiflazz_sn'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('filament.resources.ppob_transactions.fields.created_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('service_type')
                    ->label(__('filament.resources.ppob_transactions.filters.service_type'))
                    ->options([
                        'prepaid' => __('filament.options.ppob_service_type.prepaid'),
                        'postpaid' => __('filament.options.ppob_service_type.postpaid'),
                    ]),
                SelectFilter::make('payment_status')
                    ->label(__('filament.resources.ppob_transactions.filters.payment_status'))
                    ->options([
                        'unpaid' => __('filament.options.ppob_payment_status.unpaid'),
                        'paid' => __('filament.options.ppob_payment_status.paid'),
                        'expired' => __('filament.options.ppob_payment_status.expired'),
                        'reversed' => __('filament.options.ppob_payment_status.reversed'),
                        'failed' => __('filament.options.ppob_payment_status.failed'),
                    ]),
                SelectFilter::make('fulfillment_status')
                    ->label(__('filament.resources.ppob_transactions.filters.fulfillment_status'))
                    ->options([
                        'pending' => __('filament.options.ppob_fulfillment_status.pending'),
                        'processing' => __('filament.options.ppob_fulfillment_status.processing'),
                        'succeeded' => __('filament.options.ppob_fulfillment_status.succeeded'),
                        'manual_review' => __('filament.options.ppob_fulfillment_status.manual_review'),
                        'failed' => __('filament.options.ppob_fulfillment_status.failed'),
                    ]),
            ])
            ->recordActions([
                Action::make('retryFulfillment')
                    ->label(__('filament.resources.ppob_transactions.actions.retry_fulfillment'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->authorize(fn (?PpobTransaction $record): bool => $record instanceof PpobTransaction && (auth()->user()?->can('update', $record) ?? false))
                    ->visible(fn (?PpobTransaction $record): bool => $record instanceof PpobTransaction
                        && $record->payment_status === PpobTransaction::PAYMENT_PAID
                        && in_array($record->fulfillment_status, [
                            PpobTransaction::FULFILLMENT_PENDING,
                            PpobTransaction::FULFILLMENT_FAILED,
                        ], true))
                    ->action(function (PpobTransaction $record): void {
                        $transaction = app(PpobTransactionService::class)->fulfillTransaction($record->uuid);

                        Notification::make()
                            ->title(__('filament.resources.ppob_transactions.actions.retry_success_title'))
                            ->body(trans('filament.resources.ppob_transactions.actions.retry_success_body', ['status' => $transaction->fulfillment_status]))
                            ->success()
                            ->send();
                    }),
                Action::make('refreshStatus')
                    ->label(__('filament.resources.ppob_transactions.actions.refresh_status'))
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('info')
                    ->requiresConfirmation()
                    ->authorize(fn (?PpobTransaction $record): bool => $record instanceof PpobTransaction && (auth()->user()?->can('update', $record) ?? false))
                    ->action(function (PpobTransaction $record): void {
                        $transaction = app(PpobTransactionService::class)->refreshTransactionStatus($record->uuid);

                        Notification::make()
                            ->title(__('filament.resources.ppob_transactions.actions.refresh_success_title'))
                            ->body(trans('filament.resources.ppob_transactions.actions.refresh_success_body', ['status' => $transaction->resolvedStatus()]))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
