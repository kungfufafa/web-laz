<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Article;
use App\Models\MemberPrayer;
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
            Stat::make(__('filament.widgets.content_stats.total_members'), User::where('role', 'member')->count())
                ->description(__('filament.widgets.content_stats.registered_members'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('filament.widgets.content_stats.articles'), Article::count())
                ->description(__('filament.widgets.content_stats.published_count', ['count' => Article::where('is_published', true)->count()]))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make(__('filament.widgets.content_stats.videos'), Video::count())
                ->description(__('filament.widgets.content_stats.published_count', ['count' => Video::where('is_published', true)->count()]))
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('success'),

            Stat::make(__('filament.widgets.content_stats.member_prayers'), MemberPrayer::count())
                ->description(__('filament.widgets.content_stats.published_count', ['count' => MemberPrayer::where('status', 'published')->count()]))
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
        ];
    }
}
