<?php

namespace App\Filament\Resources\PpobPricingRules\Schemas;

use App\Models\PpobPricingRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PpobPricingRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament.resources.ppob_pricing_rules.sections.matching'))
                    ->description(__('filament.resources.ppob_pricing_rules.helper_text.matching'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.name'))
                            ->placeholder(__('filament.resources.ppob_pricing_rules.placeholders.name'))
                            ->required()
                            ->maxLength(120),
                        Select::make('service_type')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.service_type'))
                            ->options([
                                'prepaid' => __('filament.options.ppob_service_type.prepaid'),
                                'postpaid' => __('filament.options.ppob_service_type.postpaid'),
                            ]),
                        TextInput::make('category')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.category'))
                            ->maxLength(120),
                        TextInput::make('brand')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.brand'))
                            ->maxLength(120),
                        TextInput::make('buyer_sku_code')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.buyer_sku_code'))
                            ->maxLength(120),
                        Toggle::make('is_active')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.is_active'))
                            ->default(true)
                            ->required(),
                    ]),
                Section::make(__('filament.resources.ppob_pricing_rules.sections.pricing'))
                    ->columns(2)
                    ->components([
                        Select::make('markup_type')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.markup_type'))
                            ->options([
                                PpobPricingRule::MARKUP_FIXED => __('filament.options.ppob_markup_type.fixed'),
                                PpobPricingRule::MARKUP_PERCENT => __('filament.options.ppob_markup_type.percent'),
                            ])
                            ->default(PpobPricingRule::MARKUP_FIXED)
                            ->required(),
                        TextInput::make('markup_value')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.markup_value'))
                            ->helperText(__('filament.resources.ppob_pricing_rules.helper_text.markup_value'))
                            ->numeric()
                            ->required(),
                        TextInput::make('min_markup')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.min_markup'))
                            ->numeric(),
                        TextInput::make('max_markup')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.max_markup'))
                            ->numeric(),
                        TextInput::make('rounding_unit')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.rounding_unit'))
                            ->placeholder(__('filament.resources.ppob_pricing_rules.placeholders.rounding_unit'))
                            ->helperText(__('filament.resources.ppob_pricing_rules.helper_text.rounding_unit'))
                            ->numeric()
                            ->default(1)
                            ->required(),
                        TextInput::make('priority')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.priority'))
                            ->placeholder(__('filament.resources.ppob_pricing_rules.placeholders.priority'))
                            ->numeric()
                            ->default(100)
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('filament.resources.ppob_pricing_rules.fields.notes'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
