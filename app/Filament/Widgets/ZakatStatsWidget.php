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
            Stat::make(__('filament.widgets.zakat_stats.fitrah'), 'Rp '.number_format($zakatFitrah, 0, ',', '.'))
                ->description(__('filament.widgets.zakat_stats.fitrah_description'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make(__('filament.widgets.zakat_stats.maal'), 'Rp '.number_format($zakatMaal, 0, ',', '.'))
                ->description(__('filament.widgets.zakat_stats.maal_description'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make(__('filament.widgets.zakat_stats.profession'), 'Rp '.number_format($zakatProfesi, 0, ',', '.'))
                ->description(__('filament.widgets.zakat_stats.profession_description'))
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),
        ];
    }
}
