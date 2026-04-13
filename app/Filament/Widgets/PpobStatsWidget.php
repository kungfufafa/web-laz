<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\PpobTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PpobStatsWidget extends BaseWidget
{
    use HasShieldWidgetPermission;

    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $paidTodayAmount = (float) PpobTransaction::query()
            ->where('payment_status', PpobTransaction::PAYMENT_PAID)
            ->whereDate('paid_at', today())
            ->sum('total_amount');

        $paidTodayMargin = (float) PpobTransaction::query()
            ->where('payment_status', PpobTransaction::PAYMENT_PAID)
            ->whereDate('paid_at', today())
            ->sum('markup_amount');

        $awaitingPaymentCount = PpobTransaction::query()
            ->where('payment_status', PpobTransaction::PAYMENT_UNPAID)
            ->count();

        $needsAttentionCount = PpobTransaction::query()
            ->where(function ($query): void {
                $query
                    ->where('payment_status', PpobTransaction::PAYMENT_REVERSED)
                    ->orWhere(function ($paidQuery): void {
                        $paidQuery
                            ->where('payment_status', PpobTransaction::PAYMENT_PAID)
                            ->whereIn('fulfillment_status', [
                                PpobTransaction::FULFILLMENT_PENDING,
                                PpobTransaction::FULFILLMENT_PROCESSING,
                                PpobTransaction::FULFILLMENT_FAILED,
                                PpobTransaction::FULFILLMENT_MANUAL_REVIEW,
                            ]);
                    });
            })
            ->count();

        return [
            Stat::make(__('filament.widgets.ppob_stats.paid_today'), 'Rp '.number_format($paidTodayAmount, 0, ',', '.'))
                ->description(__('filament.widgets.ppob_stats.paid_today_description'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(__('filament.widgets.ppob_stats.margin_today'), 'Rp '.number_format($paidTodayMargin, 0, ',', '.'))
                ->description(__('filament.widgets.ppob_stats.margin_today_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make(__('filament.widgets.ppob_stats.awaiting_payment'), (string) $awaitingPaymentCount)
                ->description(__('filament.widgets.ppob_stats.awaiting_payment_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make(__('filament.widgets.ppob_stats.needs_attention'), (string) $needsAttentionCount)
                ->description(__('filament.widgets.ppob_stats.needs_attention_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
