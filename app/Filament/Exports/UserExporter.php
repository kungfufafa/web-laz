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
                ->label(__('filament.exports.users.columns.id')),
            ExportColumn::make('name')
                ->label(__('filament.exports.users.columns.name')),
            ExportColumn::make('email')
                ->label(__('filament.exports.users.columns.email')),
            ExportColumn::make('role')
                ->label(__('filament.exports.users.columns.role')),
            ExportColumn::make('phone')
                ->label(__('filament.exports.users.columns.phone')),
            ExportColumn::make('email_verified_at')
                ->label(__('filament.exports.users.columns.email_verified_at')),
            ExportColumn::make('created_at')
                ->label(__('filament.exports.users.columns.created_at')),
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
