<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Article;
use App\Models\Donation;
use App\Models\MemberPrayer;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStatsWidget extends BaseWidget
{
    use HasShieldWidgetPermission;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Member', User::where('role', 'member')->count())
                ->description('Member terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Artikel', Article::count())
                ->description(Article::where('is_published', true)->count() . ' dipublikasi')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Video', Video::count())
                ->description(Video::where('is_published', true)->count() . ' dipublikasi')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('success'),

            Stat::make('Doa Member', MemberPrayer::count())
                ->description(MemberPrayer::where('status', 'published')->count() . ' dipublikasi')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
        ];
    }
}
