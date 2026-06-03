<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class RepeatCustomersChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 10;

    protected ?string $heading = 'Новые vs повторные клиенты';

    protected ?string $description = 'По месяцам первого/повторного заказа';

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 2];

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $series = $this->analytics()->monthlyNewVsRepeatCustomers(12);

        return [
            'datasets' => [
                [
                    'label' => 'Новые',
                    'data' => $series['new_customers'],
                    'backgroundColor' => '#c9a962',
                ],
                [
                    'label' => 'Повторные',
                    'data' => $series['repeat_customers'],
                    'backgroundColor' => '#3d3428',
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true],
            ],
        ];
    }
}
