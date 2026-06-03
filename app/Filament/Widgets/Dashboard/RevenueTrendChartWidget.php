<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class RevenueTrendChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Выручка по месяцам';

    protected ?string $description = '12 месяцев, заказы с учётом в выручке';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $series = $this->analytics()->monthlyRevenue(12);

        return [
            'datasets' => [
                [
                    'label' => 'Выручка',
                    'data' => $series['values'],
                    'borderColor' => '#c9a962',
                    'backgroundColor' => 'rgba(201, 169, 98, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
