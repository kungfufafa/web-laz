<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Exports\VideoExporter;
use App\Filament\Resources\Videos\VideoResource;
use App\Models\Video;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;

class ListVideos extends ListRecords
{
    protected static string $resource = VideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->authorize(fn (): bool => auth()->user()?->can('export', Video::class) ?? false)
                ->exporter(VideoExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
