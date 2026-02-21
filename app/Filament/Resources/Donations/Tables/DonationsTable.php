<?php

namespace App\Filament\Resources\Donations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DonationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->placeholder('Guest')
                    ->searchable(),
                TextColumn::make('donor_name')
                    ->searchable(),
                TextColumn::make('category')
                    ->badge()
                    ->searchable(),
                TextColumn::make('payment_type')
                    ->searchable(),
                TextColumn::make('paymentMethod.name')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'zakat' => 'Zakat',
                        'infak' => 'Infak',
                        'sedekah' => 'Sedekah',
                    ]),
                SelectFilter::make('payment_type')
                    ->label('Jenis Donasi')
                    ->options([
                        'maal' => 'Zakat Maal',
                        'fitrah' => 'Zakat Fitrah',
                        'profesi' => 'Zakat Profesi',
                        'kemanusiaan' => 'Infak Kemanusiaan',
                        'jariyah' => 'Sedekah Jariyah',
                        'umum' => 'Umum',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                    ]),
                SelectFilter::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload(),
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
