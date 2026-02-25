<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Donation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ZakatStatsWidget extends BaseWidget
{
    use HasShieldWidgetPermission;

    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $zakatFitrah = Donation::where('category', 'zakat')
            ->where('payment_type', 'fitrah')
            ->sum('amount');

        $zakatMaal = Donation::where('category', 'zakat')
            ->where('payment_type', 'maal')
            ->sum('amount');

        $zakatProfesi = Donation::where('category', 'zakat')
            ->where('payment_type', 'profesi')
            ->sum('amount');

        return [
            Stat::make('Zakat Fitrah', 'Rp ' . number_format($zakatFitrah, 0, ',', '.'))
                ->description('Total zakat fitrah')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Zakat Maal', 'Rp ' . number_format($zakatMaal, 0, ',', '.'))
                ->description('Total zakat maal')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Zakat Profesi', 'Rp ' . number_format($zakatProfesi, 0, ',', '.'))
                ->description('Total zakat profesi')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),
        ];
    }
}
