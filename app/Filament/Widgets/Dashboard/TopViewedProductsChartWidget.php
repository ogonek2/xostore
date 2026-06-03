<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class TopViewedProductsChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 16;

    protected ?string $heading = 'Просмотры товаров (30 дней)';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $data = $this->analytics()->topViewedProductsChart(8, 30);

        return [
            'datasets' => [
                [
                    'label' => 'Просмотры',
                    'data' => $data['values'],
                    'backgroundColor' => '#6b5a3e',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
        ];
    }
}
