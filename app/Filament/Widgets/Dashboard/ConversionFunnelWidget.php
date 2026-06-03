<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\ChartWidget;

class ConversionFunnelWidget extends ChartWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 13;

    protected ?string $heading = 'Воронка конверсии (30 дней)';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $f = $this->analytics()->conversionFunnel(30);

        return [
            'datasets' => [
                [
                    'label' => 'События',
                    'data' => [
                        (float) $f['sessions'],
                        (float) $f['product_views'],
                        (float) $f['add_to_cart'],
                        (float) $f['checkout_starts'],
                        (float) $f['orders'],
                    ],
                    'backgroundColor' => ['#e8dcc0', '#d4bc82', '#c9a962', '#a8894a', '#3d3428'],
                ],
            ],
            'labels' => ['Сессии', 'Просмотры товаров', 'В корзину', 'Оформление', 'Заказы'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
        ];
    }
}
