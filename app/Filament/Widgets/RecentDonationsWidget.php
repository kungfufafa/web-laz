<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Donation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDonationsWidget extends BaseWidget
{
    use HasShieldWidgetPermission;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Donation::query()
                    ->with('paymentMethod')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.widgets.recent_donations.columns.date'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('donor_name')
                    ->label(__('filament.widgets.recent_donations.columns.donor'))
                    ->searchable()
                    ->default(__('filament.common.anonymous')),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('filament.widgets.recent_donations.columns.category'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.donation_category.'.$state)),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('filament.widgets.recent_donations.columns.amount'))
                    ->money('IDR')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label(__('filament.widgets.recent_donations.columns.payment_method'))
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.widgets.recent_donations.columns.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.donation_status.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->heading(__('filament.widgets.recent_donations.heading'));
    }
}
