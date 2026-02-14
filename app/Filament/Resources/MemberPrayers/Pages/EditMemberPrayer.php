<?php

namespace App\Filament\Resources\MemberPrayers\Pages;

use App\Filament\Resources\MemberPrayers\MemberPrayerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberPrayer extends EditRecord
{
    protected static string $resource = MemberPrayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
