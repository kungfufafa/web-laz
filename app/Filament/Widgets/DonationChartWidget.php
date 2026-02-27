<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Donation;
use Filament\Widgets\ChartWidget;

class DonationChartWidget extends ChartWidget
{
    use HasShieldWidgetPermission;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('filament.widgets.donation_chart.heading');
    }

    protected function getData(): array
    {
        $data = collect(range(6, 0))->map(function ($daysAgo) {
            return Donation::whereDate('created_at', today()->subDays($daysAgo))
                ->sum('amount');
        });

        return [
            'datasets' => [
                [
                    'label' => __('filament.widgets.donation_chart.total_label'),
                    'data' => $data->toArray(),
                    'borderColor' => 'rgb(251, 191, 36)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => collect(range(6, 0))->map(function ($daysAgo) {
                return today()->subDays($daysAgo)->translatedFormat('d M');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
