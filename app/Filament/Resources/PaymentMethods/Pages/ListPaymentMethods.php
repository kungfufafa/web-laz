<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Exports\PaymentMethodExporter;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use App\Models\PaymentMethod;
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
                ->authorize(fn (): bool => auth()->user()?->can('export', PaymentMethod::class) ?? false)
                ->exporter(PaymentMethodExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
