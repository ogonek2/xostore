<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class BrandsRevenueChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 17;

    protected ?string $heading = 'Выручка по брендам';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $data = $this->analytics()->topBrandsByRevenue(8);

        return [
            'datasets' => [
                [
                    'data' => $data['values'],
                    'backgroundColor' => $this->chartPalette(),
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
}
