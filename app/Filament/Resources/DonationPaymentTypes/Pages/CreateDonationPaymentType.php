<?php

namespace App\Filament\Resources\DonationPaymentTypes\Pages;

use App\Filament\Resources\DonationPaymentTypes\DonationPaymentTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateDonationPaymentType extends CreateRecord
{
    protected static string $resource = DonationPaymentTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $keySource = trim((string) ($data['key'] ?? ''));

        if ($keySource === '') {
            $keySource = (string) ($data['label'] ?? '');
        }

        $normalizedKey = Str::slug($keySource);

        $data['key'] = $normalizedKey !== ''
            ? $normalizedKey
            : Str::lower(Str::random(12));

        return $data;
    }
}
