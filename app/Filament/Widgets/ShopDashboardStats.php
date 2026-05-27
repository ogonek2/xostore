<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\ShopEventType;
use App\Enums\ConsultationStatus;
use App\Models\ConsultationRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShopEvent;
use App\Models\ShopVisitorSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ShopDashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenue = (float) Order::query()
            ->whereIn('status', [
                OrderStatus::Confirmed->value,
                OrderStatus::Processing->value,
                OrderStatus::Shipped->value,
                OrderStatus::Completed->value,
            ])
            ->sum('total');

        $revenueMonth = (float) Order::query()
            ->where('placed_at', '>=', now()->startOfMonth())
            ->whereNotIn('status', [OrderStatus::Cancelled->value])
            ->sum('total');

        $sessionsWeek = ShopVisitorSession::query()
            ->where('last_activity_at', '>=', now()->subDays(7))
            ->count();

        $topProduct = ShopEvent::query()
            ->select('product_id', DB::raw('count(*) as views'))
            ->where('event_type', ShopEventType::ProductView->value)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->first();

        $topProductName = $topProduct
            ? (Product::query()->find($topProduct->product_id)?->translate('name', 'pl') ?? '#'.$topProduct->product_id)
            : '—';

        $cartAdds = ShopEvent::query()
            ->where('event_type', ShopEventType::AddToCart->value)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Выручка (всего)', number_format($revenue, 2, ',', ' ').' PLN'),
            Stat::make('Выручка (месяц)', number_format($revenueMonth, 2, ',', ' ').' PLN'),
            Stat::make('Сессии (7 дней)', (string) $sessionsWeek),
            Stat::make('В корзину (7 дней)', (string) $cartAdds),
            Stat::make('Хит (30 дней)', $topProductName),
            Stat::make('Новые консультации', (string) ConsultationRequest::query()->where('status', ConsultationStatus::New->value)->count()),
        ];
    }
}
