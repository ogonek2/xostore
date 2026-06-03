<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CartKpiWidget extends StatsOverviewWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 21;

    protected ?string $heading = 'Корзины и консультации';

    protected function getStats(): array
    {
        $cart = $this->analytics()->cartMetrics();
        $a = $this->analytics();

        return [
            Stat::make('Корзины', $a->formatNumber($cart['carts']))
                ->description('Всего в БД')
                ->color('gray'),
            Stat::make('Товаров в корзинах', $a->formatNumber($cart['cart_items']))
                ->description('Суммарное кол-во')
                ->color('warning'),
            Stat::make('Консультации', $a->formatNumber($this->analytics()->newConsultationsCount()))
                ->description('Новые заявки')
                ->color('danger'),
        ];
    }
}
