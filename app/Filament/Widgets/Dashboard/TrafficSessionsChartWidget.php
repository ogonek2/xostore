<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class TrafficSessionsChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Сессии (30 дней)';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $series = $this->analytics()->dailySessions(30);

        return [
            'datasets' => [
                [
                    'label' => 'Сессии',
                    'data' => $series['values'],
                    'borderColor' => '#6b5a3e',
                    'backgroundColor' => 'rgba(107, 90, 62, 0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
