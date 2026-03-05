<?php

namespace App\Filament\Resources\DonationCategories\Pages;

use App\Filament\Resources\DonationCategories\DonationCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDonationCategories extends ListRecords
{
    protected static string $resource = DonationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
