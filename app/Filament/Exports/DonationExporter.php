<?php

namespace App\Filament\Exports;

use App\Models\Donation;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class DonationExporter extends Exporter
{
    protected static ?string $model = Donation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('uuid')
                ->label('ID Donasi'),
            ExportColumn::make('user.name')
                ->label('Member'),
            ExportColumn::make('donor_name')
                ->label('Nama Donatur'),
            ExportColumn::make('donor_phone')
                ->label('No. HP Donatur'),
            ExportColumn::make('donor_email')
                ->label('Email Donatur'),
            ExportColumn::make('category')
                ->label('Kategori'),
            ExportColumn::make('payment_type')
                ->label('Jenis Donasi'),
            ExportColumn::make('context_label')
                ->label('Program'),
            ExportColumn::make('paymentMethod.name')
                ->label('Metode Pembayaran'),
            ExportColumn::make('amount')
                ->label('Jumlah'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('admin_note')
                ->label('Catatan Admin')
                ->enabledByDefault(false),
            ExportColumn::make('created_at')
                ->label('Tanggal Donasi'),
        ];
    }

    public function getFormats(): array
    {
        return [ExportFormat::Csv, ExportFormat::Xlsx];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your donation export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
