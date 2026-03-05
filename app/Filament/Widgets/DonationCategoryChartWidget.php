<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasShieldWidgetPermission;
use App\Models\Donation;
use App\Services\DonationCatalogService;
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

        return [
            'datasets' => [
                [
                    'label' => __('filament.widgets.donation_category_chart.total_label'),
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => collect($data->keys())
                        ->map(fn (string $category) => $this->colorForCategory($category))
                        ->toArray(),
                ],
            ],
            'labels' => $data->keys()
                ->map(fn (string $category) => app(DonationCatalogService::class)->categoryLabel($category))
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function colorForCategory(string $category): string
    {
        $primaryColors = [
            'zakat' => '#FCD34D',
            'infak' => '#60A5FA',
            'sedekah' => '#34D399',
        ];

        if (array_key_exists($category, $primaryColors)) {
            return $primaryColors[$category];
        }

        $palette = ['#F59E0B', '#10B981', '#3B82F6', '#EC4899', '#8B5CF6', '#06B6D4', '#F97316', '#84CC16'];
        $index = abs(crc32($category)) % count($palette);

        return $palette[$index];
    }
}
