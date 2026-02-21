<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Exports\PaymentMethodExporter;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;

class ListPaymentMethods extends ListRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(PaymentMethodExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
