<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Donation;
use Filament\Widgets\ChartWidget;

class DonationCategoryChartWidget extends ChartWidget
{
    use HasShieldWidgetPermission;

    protected static ?int $sort = 5;

    public function getHeading(): ?string
    {
        return __('filament.widgets.donation_category_chart.heading');
    }

    protected function getData(): array
    {
        $data = Donation::query()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $colors = [
            'zakat' => '#FCD34D',
            'infak' => '#60A5FA',
            'sedekah' => '#34D399',
        ];

        return [
            'datasets' => [
                [
                    'label' => __('filament.widgets.donation_category_chart.total_label'),
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => collect($data->keys())->map(fn ($category) => $colors[$category] ?? '#9CA3AF')->toArray(),
                ],
            ],
            'labels' => $data->keys()->map(fn ($category) => __('filament.options.donation_category.'.$category))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
