<?php

namespace App\Filament\Resources\DonationPaymentTypes\Schemas;

use App\Models\DonationPaymentType;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DonationPaymentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament.resources.donation_payment_types.sections.information'))
                    ->description(__('filament.resources.donation_payment_types.descriptions.information'))
                    ->columns(2)
                    ->components([
                        Select::make('donation_category_id')
                            ->label(__('filament.resources.donation_payment_types.fields.category'))
                            ->helperText(__('filament.resources.donation_payment_types.helper_text.category'))
                            ->relationship('category', 'label')
                            ->default(fn (): ?int => request()->integer('donation_category_id') ?: null)
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('label')
                            ->label(__('filament.resources.donation_payment_types.fields.label'))
                            ->placeholder(__('filament.resources.donation_payment_types.placeholders.label'))
                            ->helperText(__('filament.resources.donation_payment_types.helper_text.label'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, ?string $state): void {
                                if (filled($get('key'))) {
                                    return;
                                }

                                $set('key', Str::slug((string) $state));
                            })
                            ->required()
                            ->maxLength(100),
                        Toggle::make('is_active')
                            ->label(__('filament.resources.donation_payment_types.fields.is_active'))
                            ->default(true),
                        Textarea::make('description')
                            ->label(__('filament.resources.donation_payment_types.fields.description'))
                            ->rows(3)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('filament.resources.donation_payment_types.sections.rules'))
                    ->description(__('filament.resources.donation_payment_types.descriptions.rules'))
                    ->columns(2)
                    ->components([
                        Toggle::make('is_zakat_calculator')
                            ->label(__('filament.resources.donation_payment_types.fields.is_zakat_calculator'))
                            ->helperText(__('filament.resources.donation_payment_types.helper_text.is_zakat_calculator'))
                            ->default(false),
                        Repeater::make('conditions')
                            ->label(__('filament.resources.donation_payment_types.fields.conditions'))
                            ->helperText(__('filament.resources.donation_payment_types.helper_text.conditions'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament.resources.donation_payment_types.fields.condition_name'))
                                    ->placeholder(__('filament.resources.donation_payment_types.placeholders.condition_name'))
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('value')
                                    ->label(__('filament.resources.donation_payment_types.fields.condition_value'))
                                    ->placeholder(__('filament.resources.donation_payment_types.placeholders.condition_value'))
                                    ->required()
                                    ->maxLength(120),
                            ])
                            ->columns(2)
                            ->addActionLabel(__('filament.resources.donation_payment_types.actions.add_condition'))
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state): array {
                                if (! is_array($state)) {
                                    return [];
                                }

                                $rows = [];

                                foreach ($state as $key => $value) {
                                    $rows[] = [
                                        'name' => (string) $key,
                                        'value' => is_scalar($value) || $value === null
                                            ? (string) $value
                                            : (json_encode($value, JSON_UNESCAPED_UNICODE) ?: ''),
                                    ];
                                }

                                return $rows;
                            })
                            ->dehydrateStateUsing(function (?array $state): ?array {
                                if (! is_array($state)) {
                                    return null;
                                }

                                $conditions = [];

                                foreach ($state as $item) {
                                    if (! is_array($item)) {
                                        continue;
                                    }

                                    $rawName = trim((string) ($item['name'] ?? ''));
                                    $rawValue = trim((string) ($item['value'] ?? ''));

                                    if ($rawName === '' || $rawValue === '') {
                                        continue;
                                    }

                                    $conditions[Str::snake($rawName)] = $rawValue;
                                }

                                return $conditions !== [] ? $conditions : null;
                            }),
                    ]),
                Section::make(__('filament.resources.donation_payment_types.sections.advanced'))
                    ->description(__('filament.resources.donation_payment_types.descriptions.advanced'))
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        TextInput::make('key')
                            ->label(__('filament.resources.donation_payment_types.fields.key'))
                            ->placeholder(__('filament.resources.donation_payment_types.placeholders.key'))
                            ->helperText(__('filament.resources.donation_payment_types.helper_text.key'))
                            ->disabled(fn (?DonationPaymentType $record): bool => $record !== null)
                            ->alphaDash()
                            ->dehydrateStateUsing(function (?string $state, Get $get): string {
                                $normalized = is_string($state)
                                    ? strtolower(trim($state))
                                    : '';

                                if ($normalized !== '') {
                                    return $normalized;
                                }

                                $fallback = Str::slug((string) ($get('label') ?? ''));

                                return $fallback !== ''
                                    ? $fallback
                                    : Str::lower(Str::random(12));
                            })
                            ->scopedUnique(
                                model: DonationPaymentType::class,
                                column: 'key',
                                ignorable: fn (?DonationPaymentType $record): ?DonationPaymentType => $record,
                                modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                                    ->withTrashed()
                                    ->where('donation_category_id', (int) ($get('donation_category_id') ?: 0)),
                            )
                            ->maxLength(50),
                        TextInput::make('sort_order')
                            ->label(__('filament.resources.donation_payment_types.fields.sort_order'))
                            ->helperText(__('filament.resources.donation_payment_types.helper_text.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
            ]);
    }
}
