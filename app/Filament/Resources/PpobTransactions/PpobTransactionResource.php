<?php

namespace App\Filament\Resources\PpobTransactions;

use App\Filament\Resources\PpobTransactions\Pages\ListPpobTransactions;
use App\Filament\Resources\PpobTransactions\Tables\PpobTransactionsTable;
use App\Models\PpobTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PpobTransactionResource extends Resource
{
    protected static ?string $model = PpobTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    public static function getModelLabel(): string
    {
        return __('filament.resources.ppob_transactions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.ppob_transactions.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.ppob_transactions.navigation_label');
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
        return PpobTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPpobTransactions::route('/'),
        ];
    }
}
