<?php

namespace App\Filament\Resources\PpobPricingRules\Pages;

use App\Filament\Resources\PpobPricingRules\PpobPricingRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPpobPricingRules extends ListRecords
{
    protected static string $resource = PpobPricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
