<?php

namespace App\Filament\Resources\Donations\Pages;

use App\Filament\Exports\DonationExporter;
use App\Filament\Resources\Donations\DonationResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;

class ListDonations extends ListRecords
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(DonationExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
