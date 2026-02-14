<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DonationStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalDonations = Donation::sum('amount');
        $pendingDonations = Donation::where('status', 'pending')->count();
        $confirmedDonations = Donation::where('status', 'confirmed')->count();
        $todayDonations = Donation::whereDate('created_at', today())->sum('amount');

        return [
            Stat::make('Total Donasi', 'Rp ' . number_format($totalDonations, 0, ',', '.'))
                ->description('Total donasi terkumpul')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Donasi Hari Ini', 'Rp ' . number_format($todayDonations, 0, ',', '.'))
                ->description('Donasi masuk hari ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Menunggu Konfirmasi', $pendingDonations)
                ->description('Donasi pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Terkonfirmasi', $confirmedDonations)
                ->description('Donasi terverifikasi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
