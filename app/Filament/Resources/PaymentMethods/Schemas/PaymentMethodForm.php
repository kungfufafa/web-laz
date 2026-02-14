<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Services\QrisPayloadService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Metode Pembayaran')
                    ->description('Data utama metode pembayaran yang tampil di aplikasi.')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nama Metode')
                            ->placeholder('Contoh: Bank BSI / QRIS')
                            ->required(),
                        Select::make('type')
                            ->label('Tipe Metode')
                            ->options([
                                'bank' => 'Bank Transfer',
                                'qris' => 'QRIS',
                                'ewallet' => 'E-Wallet',
                            ])
                            ->required()
                            ->default('bank')
                            ->live(),
                        TextInput::make('account_number')
                            ->label('Nomor Akun / Rekening / NMID')
                            ->placeholder('Contoh: 1234567890')
                            ->required(),
                        TextInput::make('account_holder')
                            ->label('Nama Pemilik Akun')
                            ->placeholder('Contoh: Baitul Maal LAZ')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->required(),
                        FileUpload::make('logo')
                            ->label('Logo Metode')
                            ->image(),
                    ]),
                Section::make('Konfigurasi QRIS')
                    ->description('Wajib diisi jika tipe metode adalah QRIS.')
                    ->visible(fn ($get) => $get('type') === 'qris')
                    ->columns(2)
                    ->components([
                        FileUpload::make('qris_image')
                            ->label('Upload Gambar QRIS')
                            ->helperText('Upload gambar QRIS, payload EMV akan diisi otomatis.')
                            ->image()
                            ->disk('public')
                            ->directory('qris-uploads')
                            ->live()
                            ->afterStateUpdated(function ($set, $state): void {
                                $uploadedPath = is_array($state) ? ($state[0] ?? null) : $state;

                                if (! is_string($uploadedPath) || $uploadedPath === '') {
                                    $set('qris_static_payload', null);

                                    return;
                                }

                                try {
                                    $imagePath = Storage::disk('public')->path($uploadedPath);
                                    $payload = app(QrisPayloadService::class)->extractStaticPayloadFromImage($imagePath);
                                    $set('qris_static_payload', $payload);
                                } catch (InvalidArgumentException $exception) {
                                    $set('qris_static_payload', null);

                                    Notification::make()
                                        ->title('QRIS tidak valid')
                                        ->body($exception->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->columnSpanFull(),
                        Textarea::make('qris_static_payload')
                            ->label('QRIS Static Payload (EMV)')
                            ->helperText('Bisa otomatis dari upload gambar QRIS, atau diisi manual.')
                            ->rows(6)
                            ->maxLength(2048)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
