<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class OrderStatusChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 6;

    protected ?string $heading = 'Заказы по статусам';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $data = $this->analytics()->orderStatusBreakdown();

        return [
            'datasets' => [
                [
                    'data' => $data['values'],
                    'backgroundColor' => $data['colors'],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
}
