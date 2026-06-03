<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class CategoriesRevenueChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 18;

    protected ?string $heading = 'Выручка по категориям';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $data = $this->analytics()->topCategoriesByRevenue(8);

        if ($data['labels'] === []) {
            return [
                'datasets' => [['data' => [1], 'backgroundColor' => ['#e5e7eb']]],
                'labels' => ['Нет данных'],
            ];
        }

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
