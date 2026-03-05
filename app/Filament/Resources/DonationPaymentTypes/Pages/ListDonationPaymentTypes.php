<?php

namespace App\Filament\Resources\DonationPaymentTypes\Pages;

use App\Filament\Resources\DonationPaymentTypes\DonationPaymentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDonationPaymentTypes extends ListRecords
{
    protected static string $resource = DonationPaymentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
