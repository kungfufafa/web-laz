<?php

namespace App\Filament\Resources\MemberPrayers\Pages;

use App\Filament\Exports\MemberPrayerExporter;
use App\Filament\Resources\MemberPrayers\MemberPrayerResource;
use App\Models\MemberPrayer;
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
                ->authorize(fn (): bool => auth()->user()?->can('export', MemberPrayer::class) ?? false)
                ->exporter(MemberPrayerExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
