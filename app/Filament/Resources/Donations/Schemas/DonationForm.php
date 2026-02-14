<?php

namespace App\Filament\Resources\Donations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DonationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Donatur')
                    ->description('Isi data donatur terdaftar atau tamu (guest).')
                    ->columns(2)
                    ->components([
                        Select::make('user_id')
                            ->label('Pengguna Terdaftar')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('guest_token')
                            ->label('Guest Token')
                            ->maxLength(120)
                            ->placeholder('Token donatur tamu'),
                        TextInput::make('donor_name')
                            ->label('Nama Donatur')
                            ->maxLength(120),
                        TextInput::make('donor_phone')
                            ->label('No. Telepon Donatur')
                            ->maxLength(30),
                        TextInput::make('donor_email')
                            ->label('Email Donatur')
                            ->email()
                            ->maxLength(120),
                    ]),
                Section::make('Detail Transaksi')
                    ->description('Informasi pokok transaksi donasi dan verifikasinya.')
                    ->columns(2)
                    ->components([
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'zakat' => 'Zakat',
                                'infak' => 'Infak',
                                'sedekah' => 'Sedekah',
                            ])
                            ->required()
                            ->default('infak')
                            ->live()
                            ->afterStateUpdated(function ($set): void {
                                $set('payment_type', null);
                            }),
                        Select::make('payment_type')
                            ->label('Jenis Donasi')
                            ->options(fn ($get): array => match ($get('category')) {
                                'zakat' => [
                                    'maal' => 'Zakat Maal',
                                    'fitrah' => 'Zakat Fitrah',
                                    'profesi' => 'Zakat Profesi',
                                ],
                                'infak' => [
                                    'kemanusiaan' => 'Infak Kemanusiaan',
                                    'umum' => 'Infak Umum',
                                ],
                                'sedekah' => [
                                    'jariyah' => 'Sedekah Jariyah',
                                    'umum' => 'Sedekah Umum',
                                ],
                                default => [],
                            })
                            ->placeholder('Pilih jenis donasi')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        FileUpload::make('proof_image')
                            ->label('Bukti Transfer')
                            ->image(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),
                    ]),
                Section::make('Konteks Program & Kalkulator')
                    ->description('Dipakai untuk donasi infak/sedekah kontekstual dan detail kalkulator zakat.')
                    ->columns(2)
                    ->components([
                        TextInput::make('context_label')
                            ->label('Konteks Donasi')
                            ->maxLength(120),
                        TextInput::make('context_slug')
                            ->label('Slug Konteks')
                            ->maxLength(120),
                        TextInput::make('calculator_type')
                            ->label('Tipe Kalkulator')
                            ->maxLength(50),
                        Textarea::make('intention_note')
                            ->label('Catatan Niat')
                            ->maxLength(255)
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('calculator_breakdown')
                            ->label('Breakdown Kalkulator (JSON)')
                            ->helperText('Isi dengan format JSON valid. Contoh: {"nisab": 123, "wajib": true}')
                            ->rule('json')
                            ->rows(6)
                            ->columnSpanFull()
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
                Section::make('Catatan Admin')
                    ->components([
                        Textarea::make('admin_note')
                            ->label('Catatan Internal')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
