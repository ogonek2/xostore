<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NewsletterKpiWidget extends StatsOverviewWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 20;

    protected ?string $heading = 'Рассылка';

    protected function getStats(): array
    {
        $m = $this->analytics()->newsletterMetrics();
        $a = $this->analytics();

        return [
            Stat::make('Подписчики', $a->formatNumber($m['active_subscribers']))
                ->description('Активные')
                ->color('primary'),
            Stat::make('Новые (месяц)', $a->formatNumber($m['new_month']))
                ->color('success'),
            Stat::make('Кампании', $a->formatNumber($m['campaigns_sent']))
                ->description('Отправлено')
                ->color('info'),
        ];
    }
}
