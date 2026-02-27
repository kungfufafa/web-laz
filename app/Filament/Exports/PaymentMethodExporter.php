<?php

namespace App\Filament\Exports;

use App\Models\PaymentMethod;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PaymentMethodExporter extends Exporter
{
    protected static ?string $model = PaymentMethod::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('filament.exports.payment_methods.columns.id')),
            ExportColumn::make('name')
                ->label(__('filament.exports.payment_methods.columns.name')),
            ExportColumn::make('type')
                ->label(__('filament.exports.payment_methods.columns.type')),
            ExportColumn::make('account_number')
                ->label(__('filament.exports.payment_methods.columns.account_number')),
            ExportColumn::make('account_holder')
                ->label(__('filament.exports.payment_methods.columns.account_holder')),
            ExportColumn::make('is_active')
                ->label(__('filament.exports.payment_methods.columns.is_active'))
                ->formatStateUsing(fn (?bool $state): string => $state ? __('filament.common.yes') : __('filament.common.no')),
            ExportColumn::make('qris_static_payload')
                ->label(__('filament.exports.payment_methods.columns.qris_static_payload'))
                ->enabledByDefault(false),
            ExportColumn::make('qris_image')
                ->label(__('filament.exports.payment_methods.columns.qris_image'))
                ->enabledByDefault(false),
            ExportColumn::make('created_at')
                ->label(__('filament.exports.payment_methods.columns.created_at')),
        ];
    }

    public function getFormats(): array
    {
        return [ExportFormat::Csv, ExportFormat::Xlsx];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payment method export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
