<?php

namespace App\Support\Analytics;

use App\Enums\ConsultationStatus;
use App\Enums\NewsletterSubscriberStatus;
use App\Enums\ShopEventType;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\ConsultationRequest;
use App\Enums\NewsletterCampaignStatus;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShopEvent;
use App\Models\ShopVisitorSession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ShopAnalytics
{
    public function currencySymbol(): string
    {
        return (string) config('shop.currency_symbol', 'zł');
    }

    public function formatMoney(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').' '.$this->currencySymbol();
    }

    public function formatNumber(float|int|string|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', ' ');
    }

    public function formatPercent(float|int|null $value): string
    {
        return $this->formatNumber($value ?? 0, 1).'%';
    }

    public function revenueOrdersQuery(): Builder
    {
        return Order::query()->whereHas('orderStatus', fn ($q) => $q->where('counts_towards_revenue', true));
    }

    public function sumRevenue(?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = $this->revenueOrdersQuery();

        if ($from) {
            $query->where('placed_at', '>=', $from);
        }

        if ($to) {
            $query->where('placed_at', '<=', $to);
        }

        return (float) $query->sum('total');
    }

    public function countOrders(?Carbon $from = null, ?Carbon $to = null): int
    {
        $query = Order::query()->whereNotNull('placed_at');

        if ($from) {
            $query->where('placed_at', '>=', $from);
        }

        if ($to) {
            $query->where('placed_at', '<=', $to);
        }

        return (int) $query->count();
    }

    public function averageOrderValue(?Carbon $from = null): float
    {
        $count = $this->countOrders($from);

        if ($count === 0) {
            return 0.0;
        }

        return $this->sumRevenue($from) / $count;
    }

    public function sparklineRevenue(int $days = 7): array
    {
        return $this->dailySeries($days, fn (Carbon $day) => $this->sumRevenue($day->copy()->startOfDay(), $day->copy()->endOfDay()));
    }

    public function sparklineOrders(int $days = 7): array
    {
        return $this->dailySeries($days, fn (Carbon $day) => (float) $this->countOrders($day->copy()->startOfDay(), $day->copy()->endOfDay()));
    }

    public function sparklineSessions(int $days = 7): array
    {
        return $this->dailySeries($days, fn (Carbon $day) => (float) ShopVisitorSession::query()
            ->whereBetween('last_activity_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->count());
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function monthlyRevenue(int $months = 12): array
    {
        $from = now()->subMonths($months - 1)->startOfMonth();
        $rows = $this->revenueOrdersQuery()
            ->selectRaw("{$this->monthPeriodExpression()} as period, SUM(total) as revenue")
            ->where('placed_at', '>=', $from)
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('revenue', 'period');

        return $this->fillMonthSeries($from, $months, $rows);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function monthlyOrders(int $months = 12): array
    {
        $from = now()->subMonths($months - 1)->startOfMonth();
        $rows = Order::query()
            ->selectRaw("{$this->monthPeriodExpression()} as period, COUNT(*) as total")
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', $from)
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        return $this->fillMonthSeries($from, $months, $rows);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function dailySessions(int $days = 30): array
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $rows = ShopVisitorSession::query()
            ->selectRaw("{$this->dayExpression('last_activity_at')} as day, COUNT(*) as total")
            ->where('last_activity_at', '>=', $from)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        return $this->fillDaySeries($from, $days, $rows);
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<float>}>}
     */
    public function dailyTrafficEvents(int $days = 30): array
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $types = [
            ShopEventType::PageView->value => 'Просмотры страниц',
            ShopEventType::ProductView->value => 'Просмотры товаров',
            ShopEventType::AddToCart->value => 'В корзину',
            ShopEventType::CheckoutStart->value => 'Оформление',
        ];

        $raw = ShopEvent::query()
            ->selectRaw("{$this->dayExpression('created_at')} as day, event_type, COUNT(*) as total")
            ->where('created_at', '>=', $from)
            ->whereIn('event_type', array_keys($types))
            ->groupBy('day', 'event_type')
            ->get()
            ->groupBy('event_type');

        $labels = [];
        $period = CarbonPeriod::create($from->toDateString(), '1 day', now()->toDateString());
        foreach ($period as $date) {
            $labels[] = $date->format('d.m');
        }

        $datasets = [];
        foreach ($types as $type => $label) {
            $byDay = ($raw[$type] ?? collect())->pluck('total', 'day');
            $data = [];
            foreach ($period as $date) {
                $data[] = (float) ($byDay[$date->toDateString()] ?? 0);
            }
            $datasets[] = ['label' => $label, 'data' => $data];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * @return array{labels: list<string>, values: list<float>, colors: list<string>}
     */
    public function orderStatusBreakdown(): array
    {
        $statuses = OrderStatus::query()->orderBy('sort_order')->get();
        $counts = Order::query()
            ->selectRaw('order_status_id, COUNT(*) as total')
            ->groupBy('order_status_id')
            ->pluck('total', 'order_status_id');

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($statuses as $status) {
            $labels[] = $status->label('pl');
            $values[] = (float) ($counts[$status->id] ?? 0);
            $colors[] = $status->color ?: '#94a3b8';
        }

        return compact('labels', 'values', 'colors');
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function paymentMethodBreakdown(): array
    {
        $rows = Order::query()
            ->selectRaw('payment_method_id, COUNT(*) as total, SUM(total) as revenue')
            ->whereNotNull('placed_at')
            ->groupBy('payment_method_id')
            ->orderByDesc('total')
            ->get();

        $methods = PaymentMethod::query()->get()->keyBy('id');
        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $method = $methods->get($row->payment_method_id);
            $labels[] = $method?->label('pl') ?? $method?->code ?? 'Без метода';
            $values[] = (float) $row->total;
        }

        if ($labels === []) {
            $labels[] = 'Нет данных';
            $values[] = 0.0;
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{
     *     avg_ltv: float,
     *     max_ltv: float,
     *     paying_customers: int,
     *     total_customers: int,
     *     avg_orders_per_customer: float
     * }
     */
    public function ltvMetrics(): array
    {
        $rows = $this->customerAggregates();

        if ($rows->isEmpty()) {
            return [
                'avg_ltv' => 0.0,
                'max_ltv' => 0.0,
                'paying_customers' => 0,
                'total_customers' => 0,
                'avg_orders_per_customer' => 0.0,
            ];
        }

        return [
            'avg_ltv' => (float) $rows->avg('ltv'),
            'max_ltv' => (float) $rows->max('ltv'),
            'paying_customers' => $rows->count(),
            'total_customers' => $rows->count(),
            'avg_orders_per_customer' => (float) $rows->avg('orders_count'),
        ];
    }

    /**
     * @return array{
     *     repeat_customers: int,
     *     one_time_customers: int,
     *     repeat_rate: float,
     *     repeat_revenue: float,
     *     repeat_revenue_share: float
     * }
     */
    public function repeatCustomerMetrics(): array
    {
        $rows = $this->customerAggregates();
        $repeat = $rows->filter(fn ($row) => $row->orders_count > 1);
        $oneTime = $rows->filter(fn ($row) => $row->orders_count === 1);
        $totalRevenue = (float) $rows->sum('ltv');
        $repeatRevenue = (float) $repeat->sum('ltv');
        $totalCustomers = max(1, $rows->count());

        return [
            'repeat_customers' => $repeat->count(),
            'one_time_customers' => $oneTime->count(),
            'repeat_rate' => $rows->isEmpty() ? 0.0 : ($repeat->count() / $totalCustomers) * 100,
            'repeat_revenue' => $repeatRevenue,
            'repeat_revenue_share' => $totalRevenue > 0 ? ($repeatRevenue / $totalRevenue) * 100 : 0.0,
        ];
    }

    /**
     * @return array{labels: list<string>, new_customers: list<float>, repeat_customers: list<float>}
     */
    public function monthlyNewVsRepeatCustomers(int $months = 12): array
    {
        $from = now()->subMonths($months - 1)->startOfMonth();
        $orders = $this->revenueOrdersQuery()
            ->select(['id', 'email', 'placed_at', 'total'])
            ->where('placed_at', '>=', $from)
            ->orderBy('placed_at')
            ->get()
            ->groupBy(fn ($order) => strtolower(trim($order->email)));

        $labels = [];
        $newCustomers = [];
        $repeatCustomers = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $from->copy()->addMonths($i);
            $labels[] = $month->translatedFormat('M Y');

            $new = 0;
            $repeat = 0;

            foreach ($orders as $customerOrders) {
                $firstAt = $customerOrders->min('placed_at');
                $inMonth = $customerOrders->filter(
                    fn ($order) => $order->placed_at->isSameMonth($month)
                );

                if ($inMonth->isEmpty()) {
                    continue;
                }

                if ($firstAt->isSameMonth($month)) {
                    $new++;
                } else {
                    $repeat++;
                }
            }

            $newCustomers[] = (float) $new;
            $repeatCustomers[] = (float) $repeat;
        }

        return [
            'labels' => $labels,
            'new_customers' => $newCustomers,
            'repeat_customers' => $repeatCustomers,
        ];
    }

    /**
     * @return array{
     *     cancelled_count: int,
     *     cancelled_sum: float,
     *     cancellation_rate: float,
     *     completed_count: int
     * }
     */
    public function returnsMetrics(): array
    {
        $cancelledId = OrderStatus::query()->where('code', 'cancelled')->value('id');
        $totalOrders = (int) Order::query()->whereNotNull('placed_at')->count();

        $cancelledQuery = Order::query()->where('order_status_id', $cancelledId);
        $cancelledCount = $cancelledId ? (int) (clone $cancelledQuery)->count() : 0;
        $cancelledSum = $cancelledId ? (float) (clone $cancelledQuery)->sum('total') : 0.0;

        $completedCount = (int) Order::query()
            ->whereHas('orderStatus', fn ($q) => $q->where('code', 'completed'))
            ->count();

        return [
            'cancelled_count' => $cancelledCount,
            'cancelled_sum' => $cancelledSum,
            'cancellation_rate' => $totalOrders > 0 ? ($cancelledCount / $totalOrders) * 100 : 0.0,
            'completed_count' => $completedCount,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function monthlyCancelledOrders(int $months = 12): array
    {
        $cancelledId = OrderStatus::query()->where('code', 'cancelled')->value('id');
        $from = now()->subMonths($months - 1)->startOfMonth();

        if (! $cancelledId) {
            return $this->fillMonthSeries($from, $months, collect());
        }

        $rows = Order::query()
            ->selectRaw("{$this->monthPeriodExpression()} as period, COUNT(*) as total")
            ->where('order_status_id', $cancelledId)
            ->where('placed_at', '>=', $from)
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        return $this->fillMonthSeries($from, $months, $rows);
    }

    public function conversionFunnel(int $days = 30): array
    {
        $from = now()->subDays($days);
        $sessions = (int) ShopVisitorSession::query()->where('last_activity_at', '>=', $from)->count();
        $productViews = (int) ShopEvent::query()
            ->where('event_type', ShopEventType::ProductView->value)
            ->where('created_at', '>=', $from)
            ->count();
        $addToCart = (int) ShopEvent::query()
            ->where('event_type', ShopEventType::AddToCart->value)
            ->where('created_at', '>=', $from)
            ->count();
        $checkoutStarts = (int) ShopEvent::query()
            ->where('event_type', ShopEventType::CheckoutStart->value)
            ->where('created_at', '>=', $from)
            ->count();
        $orders = $this->countOrders($from);

        $base = max(1, $sessions);

        return [
            'sessions' => $sessions,
            'product_views' => $productViews,
            'add_to_cart' => $addToCart,
            'checkout_starts' => $checkoutStarts,
            'orders' => $orders,
            'session_to_order' => ($orders / $base) * 100,
            'cart_to_order' => $addToCart > 0 ? ($orders / $addToCart) * 100 : 0.0,
        ];
    }

    /**
     * @return Collection<int, object{product_name: string, revenue: float, quantity: int}>
     */
    public function topProductsByRevenue(int $limit = 10): Collection
    {
        return OrderItem::query()
            ->selectRaw('product_name, SUM(total_price) as revenue, SUM(quantity) as quantity')
            ->whereHas('order', fn ($q) => $q->whereHas('orderStatus', fn ($s) => $s->where('counts_towards_revenue', true)))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function topProductsByQuantityChart(int $limit = 8): array
    {
        $rows = OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as quantity')
            ->whereHas('order', fn ($q) => $q->whereHas('orderStatus', fn ($s) => $s->where('counts_towards_revenue', true)))
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('product_name')->map(fn ($name) => mb_strimwidth($name, 0, 28, '…'))->all(),
            'values' => $rows->pluck('quantity')->map(fn ($q) => (float) $q)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function topViewedProductsChart(int $limit = 8, int $days = 30): array
    {
        $rows = ShopEvent::query()
            ->select('product_id', DB::raw('count(*) as total'))
            ->where('event_type', ShopEventType::ProductView->value)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $product = Product::query()->find($row->product_id);
            $labels[] = mb_strimwidth($product?->translate('name', 'pl') ?? 'ID '.$row->product_id, 0, 28, '…');
            $values[] = (float) $row->total;
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function topBrandsByRevenue(int $limit = 8): array
    {
        $rows = OrderItem::query()
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
            ->where('order_statuses.counts_towards_revenue', true)
            ->selectRaw('brands.id as brand_id, SUM(order_items.total_price) as revenue')
            ->groupBy('brands.id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        $brands = Brand::query()->whereIn('id', $rows->pluck('brand_id'))->get()->keyBy('id');
        $labels = [];

        foreach ($rows as $row) {
            $brand = $brands->get($row->brand_id);
            $labels[] = $brand?->translate('name', 'pl') ?? $brand?->code ?? '—';
        }

        return [
            'labels' => $labels,
            'values' => $rows->pluck('revenue')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function topCategoriesByRevenue(int $limit = 8): array
    {
        $rows = OrderItem::query()
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.primary_category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
            ->where('order_statuses.counts_towards_revenue', true)
            ->whereNotNull('products.primary_category_id')
            ->selectRaw('categories.id as category_id, SUM(order_items.total_price) as revenue')
            ->groupBy('categories.id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        $categories = Category::query()->whereIn('id', $rows->pluck('category_id'))->get()->keyBy('id');
        $labels = [];

        foreach ($rows as $row) {
            $category = $categories->get($row->category_id);
            $labels[] = $category?->translate('name', 'pl') ?? $category?->code ?? '—';
        }

        return [
            'labels' => $labels,
            'values' => $rows->pluck('revenue')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function ordersByHour(): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpr = match ($driver) {
            'sqlite' => "CAST(strftime('%H', placed_at) AS INTEGER)",
            default => 'HOUR(placed_at)',
        };

        $rows = Order::query()
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as total")
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', now()->subDays(90))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $labels = [];
        $values = [];

        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $values[] = (float) ($rows[$h] ?? 0);
        }

        return compact('labels', 'values');
    }

    public function catalogMetrics(): array
    {
        $published = (int) Product::query()->whereNotNull('published_at')->where('status', 'published')->count();
        $featured = (int) Product::query()->where('is_featured', true)->count();
        $variants = (int) ProductVariant::query()->where('is_active', true)->count();
        $lowStock = (int) ProductVariant::query()
            ->where('is_active', true)
            ->where('stock_qty', '<=', 3)
            ->whereHas('product', fn ($q) => $q->where('track_inventory', true))
            ->count();

        return compact('published', 'featured', 'variants', 'lowStock');
    }

    public function newsletterMetrics(): array
    {
        return [
            'active_subscribers' => (int) NewsletterSubscriber::query()
                ->where('status', NewsletterSubscriberStatus::Subscribed->value)
                ->count(),
            'new_month' => (int) NewsletterSubscriber::query()
                ->where('subscribed_at', '>=', now()->startOfMonth())
                ->count(),
            'campaigns_sent' => (int) NewsletterCampaign::query()
                ->where('status', NewsletterCampaignStatus::Sent->value)
                ->count(),
        ];
    }

    public function cartMetrics(): array
    {
        return [
            'carts' => (int) Cart::query()->count(),
            'cart_items' => (int) CartItem::query()->sum('quantity'),
        ];
    }

    public function newConsultationsCount(): int
    {
        return (int) ConsultationRequest::query()
            ->where('status', ConsultationStatus::New->value)
            ->count();
    }

    /**
     * @return Collection<int, object{email: string, ltv: float, orders_count: int}>
     */
    protected function monthPeriodExpression(string $column = 'placed_at'): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    protected function dayExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "date({$column})",
            'pgsql' => "DATE({$column})",
            default => "DATE({$column})",
        };
    }

    protected function customerAggregates(): Collection
    {
        return $this->revenueOrdersQuery()
            ->selectRaw('LOWER(TRIM(email)) as email, SUM(total) as ltv, COUNT(*) as orders_count')
            ->groupBy('email')
            ->get();
    }

    /**
     * @param  callable(Carbon): (float|int)  $resolver
     * @return list<float>
     */
    protected function dailySeries(int $days, callable $resolver): array
    {
        $values = [];
        $start = now()->subDays($days - 1)->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $values[] = (float) $resolver($day);
        }

        return $values;
    }

    /**
     * @param  Collection<string, mixed>  $rows
     * @return array{labels: list<string>, values: list<float>}
     */
    protected function fillMonthSeries(Carbon $from, int $months, Collection $rows): array
    {
        $labels = [];
        $values = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $from->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $values[] = (float) ($rows[$key] ?? 0);
        }

        return compact('labels', 'values');
    }

    /**
     * @param  Collection<string, mixed>  $rows
     * @return array{labels: list<string>, values: list<float>}
     */
    protected function fillDaySeries(Carbon $from, int $days, Collection $rows): array
    {
        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('d.m');
            $values[] = (float) ($rows[$key] ?? 0);
        }

        return compact('labels', 'values');
    }
}
