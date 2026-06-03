<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PrimaryKpiWidget extends StatsOverviewWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Ключевые показатели';

    protected ?string $description = 'Выручка, заказы, трафик и средний чек';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $a = $this->analytics();
        $funnel = $a->conversionFunnel(30);

        return [
            Stat::make('Выручка (всего)', $a->formatMoney($a->sumRevenue()))
                ->description('За месяц: '.$a->formatMoney($a->sumRevenue(now()->startOfMonth())))
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->chart($a->sparklineRevenue())
                ->color('primary'),
            Stat::make('Выручка (7 дней)', $a->formatMoney($a->sumRevenue(now()->subDays(6)->startOfDay())))
                ->description('Сегодня: '.$a->formatMoney($a->sumRevenue(now()->startOfDay())))
                ->chart($a->sparklineRevenue())
                ->color('success'),
            Stat::make('Заказы (всего)', (string) $a->countOrders())
                ->description('За месяц: '.$a->formatNumber($a->countOrders(now()->startOfMonth())))
                ->descriptionIcon(Heroicon::OutlinedShoppingBag)
                ->chart($a->sparklineOrders())
                ->color('info'),
            Stat::make('Средний чек', $a->formatMoney($a->averageOrderValue()))
                ->description('За 30 дней: '.$a->formatMoney($a->averageOrderValue(now()->subDays(29))))
                ->color('warning'),
            Stat::make('Сессии (7 дней)', $a->formatNumber((int) array_sum($a->sparklineSessions())))
                ->description('Конверсия в заказ: '.$a->formatPercent($funnel['session_to_order']))
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->chart($a->sparklineSessions())
                ->color('gray'),
            Stat::make('В корзину (30 дней)', $a->formatNumber($funnel['add_to_cart']))
                ->description('Оформлений: '.$a->formatNumber($funnel['checkout_starts']))
                ->descriptionIcon(Heroicon::OutlinedShoppingCart)
                ->color('primary'),
            Stat::make('Заказы (30 дней)', $a->formatNumber($funnel['orders']))
                ->description('Корзина → заказ: '.$a->formatPercent($funnel['cart_to_order']))
                ->color('success'),
            Stat::make('Просмотры товаров', $a->formatNumber($funnel['product_views']))
                ->description('За последние 30 дней')
                ->color('info'),
        ];
    }
}
