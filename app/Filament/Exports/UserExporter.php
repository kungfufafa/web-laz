<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Nama'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('role')
                ->label('Role'),
            ExportColumn::make('phone')
                ->label('No. HP'),
            ExportColumn::make('email_verified_at')
                ->label('Email Terverifikasi'),
            ExportColumn::make('created_at')
                ->label('Tanggal Registrasi'),
        ];
    }

    public function getFormats(): array
    {
        return [ExportFormat::Csv, ExportFormat::Xlsx];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
