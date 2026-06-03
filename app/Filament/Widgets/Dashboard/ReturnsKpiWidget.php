<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReturnsKpiWidget extends StatsOverviewWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 11;

    protected ?string $heading = 'Отмены и завершения';

    protected ?string $description = 'Отменённые заказы (статус cancelled)';

    protected function getStats(): array
    {
        $m = $this->analytics()->returnsMetrics();
        $a = $this->analytics();

        return [
            Stat::make('Отменено заказов', $a->formatNumber($m['cancelled_count']))
                ->description('Сумма: '.$a->formatMoney($m['cancelled_sum']))
                ->descriptionIcon(Heroicon::OutlinedXCircle)
                ->color('danger'),
            Stat::make('Доля отмен', $a->formatPercent($m['cancellation_rate']))
                ->description('От всех заказов')
                ->color('warning'),
            Stat::make('Завершено', $a->formatNumber($m['completed_count']))
                ->description('Статус completed')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
        ];
    }
}
