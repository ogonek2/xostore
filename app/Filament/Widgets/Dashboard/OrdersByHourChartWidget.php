<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class OrdersByHourChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 22;

    protected ?string $heading = 'Заказы по часам';

    protected ?string $description = 'Распределение за 90 дней';

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 2];

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $data = $this->analytics()->ordersByHour();

        return [
            'datasets' => [
                [
                    'label' => 'Заказы',
                    'data' => $data['values'],
                    'backgroundColor' => '#d4bc82',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
}
