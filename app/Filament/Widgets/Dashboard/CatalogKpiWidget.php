<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogKpiWidget extends StatsOverviewWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 19;

    protected ?string $heading = 'Каталог';

    protected function getStats(): array
    {
        $m = $this->analytics()->catalogMetrics();
        $a = $this->analytics();

        return [
            Stat::make('Опубликовано', $a->formatNumber($m['published']))
                ->description('Товаров в продаже')
                ->color('success'),
            Stat::make('Витрина', $a->formatNumber($m['featured']))
                ->description('Избранные товары')
                ->color('primary'),
            Stat::make('Варианты', $a->formatNumber($m['variants']))
                ->description('Активные SKU')
                ->color('info'),
            Stat::make('Мало на складе', $a->formatNumber($m['lowStock']))
                ->description('≤ 3 шт., учёт остатков')
                ->color('danger'),
        ];
    }
}
