<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class TopProductsQuantityChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 15;

    protected ?string $heading = 'Топ продаж (шт.)';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $data = $this->analytics()->topProductsByQuantityChart(8);

        return [
            'datasets' => [
                [
                    'label' => 'Продано',
                    'data' => $data['values'],
                    'backgroundColor' => '#c9a962',
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
