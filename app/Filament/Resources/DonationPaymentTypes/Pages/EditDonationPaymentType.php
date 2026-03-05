<?php

namespace App\Filament\Resources\DonationPaymentTypes\Pages;

use App\Filament\Resources\DonationPaymentTypes\DonationPaymentTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDonationPaymentType extends EditRecord
{
    protected static string $resource = DonationPaymentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! (bool) $this->getRecord()->is_locked),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Keep key stable after creation to avoid desynchronizing historical donation rows.
        $data['key'] = (string) $this->getRecord()->key;

        return $data;
    }
}
