<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Donation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DonationStatsWidget extends BaseWidget
{
    use HasShieldWidgetPermission;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalDonations = Donation::sum('amount');
        $pendingDonations = Donation::where('status', 'pending')->count();
        $confirmedDonations = Donation::where('status', 'confirmed')->count();
        $todayDonations = Donation::whereDate('created_at', today())->sum('amount');

        return [
            Stat::make(__('filament.widgets.donation_stats.total_donations'), 'Rp '.number_format($totalDonations, 0, ',', '.'))
                ->description(__('filament.widgets.donation_stats.total_donations_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('filament.widgets.donation_stats.today_donations'), 'Rp '.number_format($todayDonations, 0, ',', '.'))
                ->description(__('filament.widgets.donation_stats.today_donations_description'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make(__('filament.widgets.donation_stats.pending_confirmation'), $pendingDonations)
                ->description(__('filament.widgets.donation_stats.pending_confirmation_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('filament.widgets.donation_stats.confirmed'), $confirmedDonations)
                ->description(__('filament.widgets.donation_stats.confirmed_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
