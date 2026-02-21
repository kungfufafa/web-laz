<?php

namespace App\Filament\Exports;

use App\Models\Article;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ArticleExporter extends Exporter
{
    protected static ?string $model = Article::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('title')
                ->label('Judul'),
            ExportColumn::make('slug')
                ->label('Slug'),
            ExportColumn::make('is_published')
                ->label('Terbit')
                ->formatStateUsing(fn (?bool $state): string => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('published_at')
                ->label('Tanggal Terbit'),
            ExportColumn::make('created_at')
                ->label('Tanggal Dibuat'),
            ExportColumn::make('content')
                ->label('Konten')
                ->enabledByDefault(false),
        ];
    }

    public function getFormats(): array
    {
        return [ExportFormat::Csv, ExportFormat::Xlsx];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your article export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
