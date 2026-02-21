<?php

namespace App\Filament\Resources\MemberPrayers\Pages;

use App\Filament\Exports\MemberPrayerExporter;
use App\Filament\Resources\MemberPrayers\MemberPrayerResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;

class ListMemberPrayers extends ListRecords
{
    protected static string $resource = MemberPrayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(MemberPrayerExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
