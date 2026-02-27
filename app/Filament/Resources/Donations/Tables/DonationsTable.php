<?php

namespace App\Filament\Resources\Donations\Tables;

use App\Models\Donation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
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
                    ->label(__('filament.resources.donations.fields.member'))
                    ->placeholder(__('filament.resources.donations.placeholders.member_guest'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('donor_name')
                    ->label(__('filament.resources.donations.fields.donor_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->label(__('filament.resources.donations.fields.category'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.donation_category.'.$state))
                    ->searchable(),
                TextColumn::make('payment_type')
                    ->label(__('filament.resources.donations.fields.payment_type'))
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'umum' => __('filament.options.donation_payment_type.umum'),
                            default => __('filament.options.donation_payment_type.'.$state),
                        };
                    })
                    ->searchable(),
                TextColumn::make('paymentMethod.name')
                    ->label(__('filament.resources.donations.fields.payment_method'))
                    ->searchable(),
                TextColumn::make('proof_image')
                    ->label(__('filament.resources.donations.fields.proof_image'))
                    ->formatStateUsing(fn (?string $state): string => filled($state)
                        ? __('filament.resources.donations.columns.view_proof')
                        : __('filament.resources.donations.columns.no_proof'))
                    ->url(fn (Donation $record): ?string => filled($record->proof_image) ? asset('storage/'.$record->proof_image) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('amount')
                    ->label(__('filament.resources.donations.fields.amount'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament.resources.donations.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('filament.options.donation_status.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('filament.resources.donations.fields.created_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('admin_note')
                    ->label(__('filament.resources.donations.fields.admin_note'))
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.donations.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('filament.resources.donations.filters.category'))
                    ->options([
                        'zakat' => __('filament.options.donation_category.zakat'),
                        'infak' => __('filament.options.donation_category.infak'),
                        'sedekah' => __('filament.options.donation_category.sedekah'),
                    ]),
                SelectFilter::make('payment_type')
                    ->label(__('filament.resources.donations.filters.payment_type'))
                    ->options([
                        'maal' => __('filament.options.donation_payment_type.maal'),
                        'fitrah' => __('filament.options.donation_payment_type.fitrah'),
                        'profesi' => __('filament.options.donation_payment_type.profesi'),
                        'kemanusiaan' => __('filament.options.donation_payment_type.kemanusiaan'),
                        'jariyah' => __('filament.options.donation_payment_type.jariyah'),
                        'umum' => __('filament.options.donation_payment_type.umum'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('filament.resources.donations.filters.status'))
                    ->default('pending')
                    ->options([
                        'pending' => __('filament.options.donation_status.pending'),
                        'verified' => __('filament.options.donation_status.verified'),
                        'rejected' => __('filament.options.donation_status.rejected'),
                    ]),
                SelectFilter::make('payment_method_id')
                    ->label(__('filament.resources.donations.filters.payment_method'))
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('filament.resources.donations.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorize(fn (?Donation $record): bool => $record instanceof Donation && (auth()->user()?->can('approveReject', $record) ?? false))
                    ->visible(fn (?Donation $record): bool => $record instanceof Donation && $record->status === 'pending' && (auth()->user()?->can('approveReject', $record) ?? false))
                    ->schema([
                        Textarea::make('admin_note')
                            ->label(__('filament.resources.donations.fields.admin_note'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (Donation $record): array => [
                        'admin_note' => $record->admin_note,
                    ])
                    ->action(function (Donation $record, array $data): bool {
                        return $record->update([
                            'status' => 'verified',
                            'admin_note' => blank($data['admin_note'] ?? null) ? null : $data['admin_note'],
                        ]);
                    })
                    ->successNotificationTitle(__('filament.resources.donations.actions.approve_success')),
                Action::make('reject')
                    ->label(__('filament.resources.donations.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->authorize(fn (?Donation $record): bool => $record instanceof Donation && (auth()->user()?->can('approveReject', $record) ?? false))
                    ->visible(fn (?Donation $record): bool => $record instanceof Donation && $record->status === 'pending' && (auth()->user()?->can('approveReject', $record) ?? false))
                    ->schema([
                        Textarea::make('admin_note')
                            ->label(__('filament.resources.donations.fields.admin_note'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (Donation $record): array => [
                        'admin_note' => $record->admin_note,
                    ])
                    ->action(function (Donation $record, array $data): bool {
                        return $record->update([
                            'status' => 'rejected',
                            'admin_note' => blank($data['admin_note'] ?? null) ? null : $data['admin_note'],
                        ]);
                    })
                    ->successNotificationTitle(__('filament.resources.donations.actions.reject_success')),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
