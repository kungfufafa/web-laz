<?php

namespace App\Filament\Resources\DonationCategories\Pages;

use App\Filament\Resources\DonationCategories\DonationCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDonationCategory extends EditRecord
{
    protected static string $resource = DonationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! (bool) $this->getRecord()->is_locked),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['key'] = (string) $this->getRecord()->key;
        $data['requires_context'] = false;

        return $data;
    }
}
