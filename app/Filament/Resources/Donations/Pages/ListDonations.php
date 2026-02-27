<?php

namespace App\Filament\Resources\Donations\Pages;

use App\Filament\Exports\DonationExporter;
use App\Filament\Resources\Donations\DonationResource;
use App\Models\Donation;
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
                ->authorize(fn (): bool => auth()->user()?->can('export', Donation::class) ?? false)
                ->exporter(DonationExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
