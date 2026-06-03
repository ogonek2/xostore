<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LtvKpiWidget extends StatsOverviewWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 8;

    protected ?string $heading = 'LTV и клиенты';

    protected ?string $description = 'Пожизненная ценность по e-mail покупателей';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $ltv = $this->analytics()->ltvMetrics();
        $a = $this->analytics();

        return [
            Stat::make('Средний LTV', $a->formatMoney($ltv['avg_ltv']))
                ->description('На одного платящего клиента')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('primary'),
            Stat::make('Максимальный LTV', $a->formatMoney($ltv['max_ltv']))
                ->description('Лучший клиент по сумме заказов')
                ->color('success'),
            Stat::make('Платящих клиентов', $a->formatNumber($ltv['paying_customers']))
                ->description('Среднее заказов: '.$a->formatNumber($ltv['avg_orders_per_customer'], 1))
                ->color('info'),
            Stat::make('Выручка / клиент', $a->formatMoney($ltv['avg_ltv']))
                ->description('Уникальные e-mail в заказах')
                ->color('warning'),
        ];
    }
}
