<?php

namespace App\Filament\Widgets;

use App\Enums\ShopEventType;
use App\Models\Product;
use App\Models\ShopEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopViewedProductsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Просмотры товаров (30 дней)';

    protected function getData(): array
    {
        $rows = ShopEvent::query()
            ->select('product_id', DB::raw('count(*) as total'))
            ->where('event_type', ShopEventType::ProductView->value)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $product = Product::query()->find($row->product_id);
            $labels[] = $product?->translate('name', 'pl') ?? 'ID '.$row->product_id;
            $data[] = (int) $row->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Просмотры',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
