<?php

namespace App\Filament\Resources\DonationCategories;

use App\Filament\Resources\DonationCategories\Pages\CreateDonationCategory;
use App\Filament\Resources\DonationCategories\Pages\EditDonationCategory;
use App\Filament\Resources\DonationCategories\Pages\ListDonationCategories;
use App\Filament\Resources\DonationCategories\RelationManagers\PaymentTypesRelationManager;
use App\Filament\Resources\DonationCategories\Schemas\DonationCategoryForm;
use App\Filament\Resources\DonationCategories\Tables\DonationCategoriesTable;
use App\Models\DonationCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DonationCategoryResource extends Resource
{
    protected static ?string $model = DonationCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    public static function getModelLabel(): string
    {
        return __('filament.resources.donation_categories.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.donation_categories.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.donation_categories.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function form(Schema $schema): Schema
    {
        return DonationCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DonationCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentTypesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDonationCategories::route('/'),
            'create' => CreateDonationCategory::route('/create'),
            'edit' => EditDonationCategory::route('/{record}/edit'),
        ];
    }
}
