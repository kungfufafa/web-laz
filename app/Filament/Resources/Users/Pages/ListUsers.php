<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(UserExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
