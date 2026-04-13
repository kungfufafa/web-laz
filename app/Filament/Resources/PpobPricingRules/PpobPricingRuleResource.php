<?php

namespace App\Filament\Resources\PpobPricingRules;

use App\Filament\Resources\PpobPricingRules\Pages\CreatePpobPricingRule;
use App\Filament\Resources\PpobPricingRules\Pages\EditPpobPricingRule;
use App\Filament\Resources\PpobPricingRules\Pages\ListPpobPricingRules;
use App\Filament\Resources\PpobPricingRules\Schemas\PpobPricingRuleForm;
use App\Filament\Resources\PpobPricingRules\Tables\PpobPricingRulesTable;
use App\Models\PpobPricingRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PpobPricingRuleResource extends Resource
{
    protected static ?string $model = PpobPricingRule::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static function getModelLabel(): string
    {
        return __('filament.resources.ppob_pricing_rules.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.ppob_pricing_rules.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.ppob_pricing_rules.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function form(Schema $schema): Schema
    {
        return PpobPricingRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PpobPricingRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPpobPricingRules::route('/'),
            'create' => CreatePpobPricingRule::route('/create'),
            'edit' => EditPpobPricingRule::route('/{record}/edit'),
        ];
    }
}
