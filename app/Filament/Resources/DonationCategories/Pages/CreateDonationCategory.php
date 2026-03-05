<?php

namespace App\Filament\Resources\DonationCategories\Pages;

use App\Filament\Resources\DonationCategories\DonationCategoryResource;
use App\Models\DonationCategory;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateDonationCategory extends CreateRecord
{
    protected static string $resource = DonationCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['key'] = $this->generateUniqueCategoryKey((string) ($data['label'] ?? ''));
        $data['sort_order'] = ((int) DonationCategory::query()->max('sort_order')) + 1;
        $data['requires_context'] = false;

        return $data;
    }

    private function generateUniqueCategoryKey(string $label): string
    {
        $baseKey = Str::slug($label);

        if ($baseKey === '') {
            $baseKey = Str::lower(Str::random(12));
        }

        $key = $baseKey;
        $suffix = 2;

        while (DonationCategory::withTrashed()->where('key', $key)->exists()) {
            $key = "{$baseKey}-{$suffix}";
            $suffix++;
        }

        return $key;
    }
}
