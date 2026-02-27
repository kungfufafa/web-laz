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
                ->label(__('filament.exports.articles.columns.id')),
            ExportColumn::make('title')
                ->label(__('filament.exports.articles.columns.title')),
            ExportColumn::make('slug')
                ->label(__('filament.exports.articles.columns.slug')),
            ExportColumn::make('is_published')
                ->label(__('filament.exports.articles.columns.is_published'))
                ->formatStateUsing(fn (?bool $state): string => $state ? __('filament.common.yes') : __('filament.common.no')),
            ExportColumn::make('published_at')
                ->label(__('filament.exports.articles.columns.published_at')),
            ExportColumn::make('created_at')
                ->label(__('filament.exports.articles.columns.created_at')),
            ExportColumn::make('content')
                ->label(__('filament.exports.articles.columns.content'))
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
