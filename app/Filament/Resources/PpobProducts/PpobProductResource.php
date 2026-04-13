<?php

namespace App\Filament\Resources\PpobProducts;

use App\Filament\Resources\PpobProducts\Pages\ListPpobProducts;
use App\Filament\Resources\PpobProducts\Tables\PpobProductsTable;
use App\Models\PpobProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PpobProductResource extends Resource
{
    protected static ?string $model = PpobProduct::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    public static function getModelLabel(): string
    {
        return __('filament.resources.ppob_products.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.ppob_products.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.ppob_products.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return PpobProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPpobProducts::route('/'),
        ];
    }
}
