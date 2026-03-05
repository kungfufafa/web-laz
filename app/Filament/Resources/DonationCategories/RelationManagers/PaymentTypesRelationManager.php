<?php

namespace App\Filament\Resources\DonationCategories\RelationManagers;

use App\Models\DonationPaymentType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentTypes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.resources.donation_categories.relations.payment_types');
    }

    public function form(Schema $schema): Schema
    {
        $components = [
            TextInput::make('label')
                ->label(__('filament.resources.donation_payment_types.fields.label'))
                ->placeholder(__('filament.resources.donation_payment_types.placeholders.label'))
                ->helperText(__('filament.resources.donation_payment_types.helper_text.label'))
                ->required()
                ->maxLength(100)
                ->columnSpan(2),
            Toggle::make('is_active')
                ->label(__('filament.resources.donation_payment_types.fields.is_active'))
                ->default(true)
                ->columnSpan(2),
            Textarea::make('description')
                ->label(__('filament.resources.donation_payment_types.fields.description'))
                ->rows(2)
                ->maxLength(255)
                ->columnSpan(2),
            Hidden::make('key')
                ->dehydrateStateUsing(function (mixed $state, ?Model $record, Get $get): string {
                    if ($record instanceof DonationPaymentType) {
                        return (string) $record->key;
                    }

                    return $this->generateUniquePaymentTypeKey((string) ($get('label') ?? ''));
                }),
            Hidden::make('sort_order')
                ->dehydrateStateUsing(function (mixed $state, ?Model $record): int {
                    if ($record instanceof DonationPaymentType) {
                        return (int) $record->sort_order;
                    }

                    return $this->nextSortOrder();
                }),
        ];

        if ($this->isZakatCategory()) {
            $components[] = Toggle::make('is_zakat_calculator')
                ->label(__('filament.resources.donation_payment_types.fields.is_zakat_calculator'))
                ->helperText(__('filament.resources.donation_payment_types.helper_text.is_zakat_calculator'))
                ->default(true)
                ->formatStateUsing(function (mixed $state, ?DonationPaymentType $record): bool {
                    if ($record instanceof DonationPaymentType) {
                        return (bool) $record->is_zakat_calculator;
                    }

                    return true;
                })
                ->columnSpan(2);

            $components[] = TextInput::make('condition_min_amount')
                ->label(__('filament.resources.donation_payment_types.fields.min_amount'))
                ->helperText(__('filament.resources.donation_payment_types.helper_text.min_amount'))
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->dehydrated(false)
                ->formatStateUsing(fn (mixed $state, ?DonationPaymentType $record): ?string => $this->conditionNumber($record, 'min_amount'))
                ->columnSpan(2);

            $components[] = TextInput::make('condition_max_amount')
                ->label(__('filament.resources.donation_payment_types.fields.max_amount'))
                ->helperText(__('filament.resources.donation_payment_types.helper_text.max_amount'))
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->dehydrated(false)
                ->formatStateUsing(fn (mixed $state, ?DonationPaymentType $record): ?string => $this->conditionNumber($record, 'max_amount'))
                ->columnSpan(2);

            $components[] = Toggle::make('condition_require_context')
                ->label(__('filament.resources.donation_payment_types.fields.require_context'))
                ->default(false)
                ->dehydrated(false)
                ->formatStateUsing(fn (mixed $state, ?DonationPaymentType $record): bool => $this->conditionBoolean($record, 'require_context'))
                ->columnSpan(2);

            $components[] = Toggle::make('condition_require_intention_note')
                ->label(__('filament.resources.donation_payment_types.fields.require_intention_note'))
                ->default(false)
                ->dehydrated(false)
                ->formatStateUsing(fn (mixed $state, ?DonationPaymentType $record): bool => $this->conditionBoolean($record, 'require_intention_note'))
                ->columnSpan(2);

            $components[] = Hidden::make('raw_conditions')
                ->dehydrated(false)
                ->formatStateUsing(function (mixed $state, ?DonationPaymentType $record): array {
                    return is_array($record?->conditions) ? $record->conditions : [];
                });

            $components[] = Hidden::make('conditions')
                ->dehydrateStateUsing(fn (mixed $state, Get $get): ?array => $this->buildZakatConditions($get));
        } else {
            $components[] = Hidden::make('is_zakat_calculator')
                ->dehydrateStateUsing(fn (): bool => false);

            $components[] = Hidden::make('conditions')
                ->dehydrateStateUsing(fn (): ?array => null);
        }

        return $schema
            ->columns(2)
            ->components($components);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label(__('filament.resources.donation_payment_types.fields.label'))
                    ->searchable(),
                TextColumn::make('key')
                    ->label(__('filament.resources.donation_payment_types.fields.key'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_locked')
                    ->label(__('filament.resources.donation_payment_types.fields.is_locked'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('filament.resources.donation_payment_types.fields.is_active'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('filament.resources.donation_payment_types.fields.updated_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->slideOver(),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver(),
                DeleteAction::make()
                    ->visible(fn (DonationPaymentType $record): bool => ! $record->is_locked),
            ]);
    }

    private function nextSortOrder(): int
    {
        return ((int) DonationPaymentType::query()
            ->where('donation_category_id', $this->getOwnerRecord()->getKey())
            ->max('sort_order')) + 1;
    }

    private function generateUniquePaymentTypeKey(string $label, mixed $ignoreRecordId = null): string
    {
        $baseKey = Str::slug($label);

        if ($baseKey === '') {
            $baseKey = Str::lower(Str::random(12));
        }

        $query = DonationPaymentType::withTrashed()
            ->where('donation_category_id', $this->getOwnerRecord()->getKey());

        if (filled($ignoreRecordId)) {
            $query->whereKeyNot($ignoreRecordId);
        }

        $key = $baseKey;
        $suffix = 2;

        while ((clone $query)->where('key', $key)->exists()) {
            $key = "{$baseKey}-{$suffix}";
            $suffix++;
        }

        return $key;
    }

    private function isZakatCategory(): bool
    {
        return strtolower(trim((string) $this->getOwnerRecord()->getAttribute('key'))) === 'zakat';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildZakatConditions(Get $get): ?array
    {
        $conditions = [];
        $knownKeys = ['min_amount', 'max_amount', 'require_context', 'require_intention_note'];
        $rawConditions = $get('raw_conditions');

        if (is_array($rawConditions)) {
            foreach ($rawConditions as $key => $value) {
                if (! is_string($key) || in_array($key, $knownKeys, true)) {
                    continue;
                }

                $conditions[$key] = $value;
            }
        }

        $minAmount = $this->normalizedConditionAmount($get('condition_min_amount'));
        if ($minAmount !== null) {
            $conditions['min_amount'] = $minAmount;
        }

        $maxAmount = $this->normalizedConditionAmount($get('condition_max_amount'));
        if ($maxAmount !== null) {
            $conditions['max_amount'] = $maxAmount;
        }

        if ((bool) ($get('condition_require_context') ?? false)) {
            $conditions['require_context'] = true;
        }

        if ((bool) ($get('condition_require_intention_note') ?? false)) {
            $conditions['require_intention_note'] = true;
        }

        return $conditions !== [] ? $conditions : null;
    }

    private function conditionNumber(?DonationPaymentType $record, string $key): ?string
    {
        $value = is_array($record?->conditions) ? ($record->conditions[$key] ?? null) : null;

        if (! is_numeric($value)) {
            return null;
        }

        return (string) ((float) $value);
    }

    private function conditionBoolean(?DonationPaymentType $record, string $key): bool
    {
        $value = is_array($record?->conditions) ? ($record->conditions[$key] ?? null) : null;

        return (bool) $value;
    }

    private function normalizedConditionAmount(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $normalized = (float) $value;

        if ($normalized < 0) {
            return null;
        }

        return $normalized;
    }

}
