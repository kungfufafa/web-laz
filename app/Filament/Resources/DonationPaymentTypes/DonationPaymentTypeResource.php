<?php

namespace App\Filament\Resources\DonationPaymentTypes;

use App\Filament\Resources\DonationPaymentTypes\Pages\CreateDonationPaymentType;
use App\Filament\Resources\DonationPaymentTypes\Pages\EditDonationPaymentType;
use App\Filament\Resources\DonationPaymentTypes\Pages\ListDonationPaymentTypes;
use App\Filament\Resources\DonationPaymentTypes\Schemas\DonationPaymentTypeForm;
use App\Filament\Resources\DonationPaymentTypes\Tables\DonationPaymentTypesTable;
use App\Models\DonationPaymentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DonationPaymentTypeResource extends Resource
{
    protected static ?string $model = DonationPaymentType::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    public static function getModelLabel(): string
    {
        return __('filament.resources.donation_payment_types.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.donation_payment_types.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.donation_payment_types.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function form(Schema $schema): Schema
    {
        return DonationPaymentTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DonationPaymentTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDonationPaymentTypes::route('/'),
            'create' => CreateDonationPaymentType::route('/create'),
            'edit' => EditDonationPaymentType::route('/{record}/edit'),
        ];
    }
}
