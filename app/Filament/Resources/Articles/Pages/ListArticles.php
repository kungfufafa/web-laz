<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Exports\ArticleExporter;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(ArticleExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx]),
        ];
    }
}
