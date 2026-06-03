<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class OrdersTrendChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Заказы по месяцам';

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 2];

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $series = $this->analytics()->monthlyOrders(12);

        return [
            'datasets' => [
                [
                    'label' => 'Заказы',
                    'data' => $series['values'],
                    'backgroundColor' => '#a8894a',
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
