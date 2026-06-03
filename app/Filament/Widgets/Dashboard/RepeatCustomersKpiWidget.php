<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RepeatCustomersKpiWidget extends StatsOverviewWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 9;

    protected ?string $heading = 'Повторные продажи';

    protected function getStats(): array
    {
        $m = $this->analytics()->repeatCustomerMetrics();
        $a = $this->analytics();

        return [
            Stat::make('Повторные клиенты', $a->formatNumber($m['repeat_customers']))
                ->description('2+ заказа')
                ->descriptionIcon(Heroicon::OutlinedArrowPath)
                ->color('success'),
            Stat::make('Разовые клиенты', $a->formatNumber($m['one_time_customers']))
                ->description('1 заказ')
                ->color('gray'),
            Stat::make('Доля повторных', $a->formatPercent($m['repeat_rate']))
                ->description('От всех платящих')
                ->color('primary'),
            Stat::make('Выручка повторных', $a->formatMoney($m['repeat_revenue']))
                ->description($a->formatPercent($m['repeat_revenue_share']).' от выручки')
                ->color('warning'),
        ];
    }
}
