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
                Section::make(__('filament.resources.payment_methods.sections.information'))
                    ->description(__('filament.resources.payment_methods.descriptions.information'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('filament.resources.payment_methods.fields.name'))
                            ->placeholder(__('filament.resources.payment_methods.placeholders.name'))
                            ->required(),
                        Select::make('type')
                            ->label(__('filament.resources.payment_methods.fields.type'))
                            ->options([
                                'bank' => __('filament.options.payment_method_type.bank'),
                                'qris' => __('filament.options.payment_method_type.qris'),
                                'ewallet' => __('filament.options.payment_method_type.ewallet'),
                            ])
                            ->required()
                            ->default('bank')
                            ->live(),
                        TextInput::make('account_number')
                            ->label(__('filament.resources.payment_methods.fields.account_number'))
                            ->placeholder(__('filament.resources.payment_methods.placeholders.account_number'))
                            ->required(),
                        TextInput::make('account_holder')
                            ->label(__('filament.resources.payment_methods.fields.account_holder'))
                            ->placeholder(__('filament.resources.payment_methods.placeholders.account_holder'))
                            ->required(),
                        Toggle::make('is_active')
                            ->label(__('filament.resources.payment_methods.fields.is_active'))
                            ->required(),
                        FileUpload::make('logo')
                            ->label(__('filament.resources.payment_methods.fields.logo'))
                            ->image(),
                    ]),
                Section::make(__('filament.resources.payment_methods.sections.qris_configuration'))
                    ->description(__('filament.resources.payment_methods.descriptions.qris_configuration'))
                    ->visible(fn ($get) => $get('type') === 'qris')
                    ->columns(2)
                    ->components([
                        FileUpload::make('qris_image')
                            ->label(__('filament.resources.payment_methods.fields.qris_image'))
                            ->helperText(__('filament.resources.payment_methods.helper_text.qris_image'))
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
                                        ->title(__('filament.notifications.invalid_qris'))
                                        ->body($exception->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->columnSpanFull(),
                        Textarea::make('qris_static_payload')
                            ->label(__('filament.resources.payment_methods.fields.qris_static_payload'))
                            ->helperText(__('filament.resources.payment_methods.helper_text.qris_static_payload'))
                            ->rows(6)
                            ->maxLength(2048)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
