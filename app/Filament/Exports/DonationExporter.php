<?php

namespace App\Filament\Exports;

use App\Models\Donation;
use App\Services\DonationCatalogService;
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
                ->label(__('filament.exports.donations.columns.id')),
            ExportColumn::make('user.name')
                ->label(__('filament.exports.donations.columns.member')),
            ExportColumn::make('donor_name')
                ->label(__('filament.exports.donations.columns.donor_name')),
            ExportColumn::make('donor_phone')
                ->label(__('filament.exports.donations.columns.donor_phone')),
            ExportColumn::make('donor_email')
                ->label(__('filament.exports.donations.columns.donor_email')),
            ExportColumn::make('category')
                ->label(__('filament.exports.donations.columns.category'))
                ->formatStateUsing(fn (?string $state): string => app(DonationCatalogService::class)->categoryLabel($state)),
            ExportColumn::make('payment_type')
                ->label(__('filament.exports.donations.columns.payment_type'))
                ->formatStateUsing(fn (?string $state): string => app(DonationCatalogService::class)
                    ->paymentTypeLabel($state)),
            ExportColumn::make('context_label')
                ->label(__('filament.exports.donations.columns.program')),
            ExportColumn::make('paymentMethod.name')
                ->label(__('filament.exports.donations.columns.payment_method')),
            ExportColumn::make('amount')
                ->label(__('filament.exports.donations.columns.amount')),
            ExportColumn::make('status')
                ->label(__('filament.exports.donations.columns.status')),
            ExportColumn::make('admin_note')
                ->label(__('filament.exports.donations.columns.admin_note'))
                ->enabledByDefault(false),
            ExportColumn::make('created_at')
                ->label(__('filament.exports.donations.columns.created_at')),
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
