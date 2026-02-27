<?php

namespace App\Filament\Exports;

use App\Models\MemberPrayer;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class MemberPrayerExporter extends Exporter
{
    protected static ?string $model = MemberPrayer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('filament.exports.member_prayers.columns.id')),
            ExportColumn::make('user.name')
                ->label(__('filament.exports.member_prayers.columns.member')),
            ExportColumn::make('content')
                ->label(__('filament.exports.member_prayers.columns.content')),
            ExportColumn::make('is_anonymous')
                ->label(__('filament.exports.member_prayers.columns.is_anonymous'))
                ->formatStateUsing(fn (?bool $state): string => $state ? __('filament.common.yes') : __('filament.common.no')),
            ExportColumn::make('likes_count')
                ->label(__('filament.exports.member_prayers.columns.likes_count')),
            ExportColumn::make('status')
                ->label(__('filament.exports.member_prayers.columns.status')),
            ExportColumn::make('created_at')
                ->label(__('filament.exports.member_prayers.columns.created_at')),
        ];
    }

    public function getFormats(): array
    {
        return [ExportFormat::Csv, ExportFormat::Xlsx];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your member prayer export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
