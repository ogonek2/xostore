<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class TrafficEventsChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 5;

    protected ?string $heading = 'Воронка событий';

    protected ?string $description = 'Просмотры, корзина и оформление за 30 дней';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $series = $this->analytics()->dailyTrafficEvents(30);
        $colors = $this->chartPalette();
        $datasets = [];

        foreach ($series['datasets'] as $index => $dataset) {
            $color = $colors[$index % count($colors)];
            $datasets[] = [
                'label' => $dataset['label'],
                'data' => $dataset['data'],
                'borderColor' => $color,
                'backgroundColor' => $color.'33',
                'fill' => false,
                'tension' => 0.25,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $series['labels'],
        ];
    }
}
