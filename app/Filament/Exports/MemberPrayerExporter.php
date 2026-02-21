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
                ->label('ID'),
            ExportColumn::make('user.name')
                ->label('Member'),
            ExportColumn::make('content')
                ->label('Isi Doa'),
            ExportColumn::make('is_anonymous')
                ->label('Anonim')
                ->formatStateUsing(fn (?bool $state): string => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('likes_count')
                ->label('Jumlah Amin'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('created_at')
                ->label('Tanggal Dibuat'),
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
