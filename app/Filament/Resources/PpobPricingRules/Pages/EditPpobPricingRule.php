<?php

namespace App\Filament\Resources\PpobPricingRules\Pages;

use App\Filament\Resources\PpobPricingRules\PpobPricingRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPpobPricingRule extends EditRecord
{
    protected static string $resource = PpobPricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
