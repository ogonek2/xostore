<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class PaymentMethodsChartWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 7;

    protected ?string $heading = 'Способы оплаты';

    protected ?string $description = 'Количество заказов';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $data = $this->analytics()->paymentMethodBreakdown();

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
