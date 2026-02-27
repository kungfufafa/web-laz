<?php

namespace App\Filament\Exports;

use App\Models\Video;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class VideoExporter extends Exporter
{
    protected static ?string $model = Video::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('filament.exports.videos.columns.id')),
            ExportColumn::make('title')
                ->label(__('filament.exports.videos.columns.title')),
            ExportColumn::make('youtube_id')
                ->label(__('filament.exports.videos.columns.youtube_id')),
            ExportColumn::make('description')
                ->label(__('filament.exports.videos.columns.description'))
                ->enabledByDefault(false),
            ExportColumn::make('is_published')
                ->label(__('filament.exports.videos.columns.is_published'))
                ->formatStateUsing(fn (?bool $state): string => $state ? __('filament.common.yes') : __('filament.common.no')),
            ExportColumn::make('created_at')
                ->label(__('filament.exports.videos.columns.created_at')),
        ];
    }

    public function getFormats(): array
    {
        return [ExportFormat::Csv, ExportFormat::Xlsx];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your video export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
