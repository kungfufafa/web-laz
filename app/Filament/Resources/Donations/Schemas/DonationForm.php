<?php

namespace App\Filament\Resources\Donations\Schemas;

use App\Models\Donation;
use App\Services\DonationCatalogService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DonationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament.resources.donations.sections.donor_data'))
                    ->description(__('filament.resources.donations.descriptions.donor_data'))
                    ->columns(2)
                    ->components([
                        Select::make('user_id')
                            ->label(__('filament.resources.donations.fields.registered_user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('guest_token')
                            ->label(__('filament.resources.donations.fields.guest_token'))
                            ->maxLength(120)
                            ->placeholder(__('filament.resources.donations.placeholders.guest_token')),
                        TextInput::make('donor_name')
                            ->label(__('filament.resources.donations.fields.donor_name'))
                            ->maxLength(120),
                        TextInput::make('donor_phone')
                            ->label(__('filament.resources.donations.fields.donor_phone'))
                            ->maxLength(30),
                        TextInput::make('donor_email')
                            ->label(__('filament.resources.donations.fields.donor_email'))
                            ->email()
                            ->maxLength(120),
                    ]),
                Section::make(__('filament.resources.donations.sections.transaction_details'))
                    ->description(__('filament.resources.donations.descriptions.transaction_details'))
                    ->columns(2)
                    ->components([
                        Select::make('payment_method_id')
                            ->label(__('filament.resources.donations.fields.payment_method'))
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category')
                            ->label(__('filament.resources.donations.fields.category'))
                            ->options(fn (): array => app(DonationCatalogService::class)->categoriesForSelect())
                            ->required()
                            ->default(function (): ?string {
                                $options = app(DonationCatalogService::class)->categoriesForSelect();

                                return array_key_first($options);
                            })
                            ->live()
                            ->afterStateUpdated(function ($set): void {
                                $set('payment_type', null);
                            }),
                        Select::make('payment_type')
                            ->label(__('filament.resources.donations.fields.payment_type'))
                            ->options(fn (Get $get): array => app(DonationCatalogService::class)
                                ->paymentTypesForCategory((string) ($get('category') ?? '')))
                            ->placeholder(__('filament.resources.donations.placeholders.payment_type'))
                            ->required(),
                        TextInput::make('amount')
                            ->label(__('filament.resources.donations.fields.amount'))
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        FileUpload::make('proof_image')
                            ->label(__('filament.resources.donations.fields.proof_image'))
                            ->disk(Donation::PROOF_IMAGE_DISK)
                            ->directory(Donation::PROOF_IMAGE_DIRECTORY)
                            ->visibility('private')
                            ->image(),
                        Select::make('status')
                            ->label(__('filament.resources.donations.fields.status'))
                            ->options([
                                'pending' => __('filament.options.donation_status.pending'),
                                'verified' => __('filament.options.donation_status.verified'),
                                'rejected' => __('filament.options.donation_status.rejected'),
                            ])
                            ->required()
                            ->default('pending'),
                    ]),
                Section::make(__('filament.resources.donations.sections.program_context'))
                    ->description(__('filament.resources.donations.descriptions.program_context'))
                    ->columns(2)
                    ->components([
                        Textarea::make('intention_note')
                            ->label(__('filament.resources.donations.fields.intention_note'))
                            ->maxLength(255)
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('calculator_breakdown')
                            ->label(__('filament.resources.donations.fields.calculator_breakdown'))
                            ->helperText(__('filament.resources.donations.helper_text.calculator_breakdown'))
                            ->rule('json')
                            ->rows(6)
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => strtolower(trim((string) ($get('category') ?? ''))) === 'zakat')
                            ->formatStateUsing(fn ($state): ?string => is_array($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : $state)
                            ->dehydrateStateUsing(function (?string $state): ?array {
                                if (blank($state)) {
                                    return null;
                                }

                                $decoded = json_decode($state, true);

                                return is_array($decoded) ? $decoded : null;
                            }),
                    ]),
                Section::make(__('filament.resources.donations.sections.admin_notes'))
                    ->components([
                        Textarea::make('admin_note')
                            ->label(__('filament.resources.donations.fields.admin_note'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
