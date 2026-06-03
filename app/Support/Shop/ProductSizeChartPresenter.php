<?php

namespace App\Support\Shop;

use App\Models\Product;

final class ProductSizeChartPresenter
{
    /**
     * @return array{unit: ?string, headers: list<string>, rows: list<array<string, ?string>>}|null
     */
    public static function forProduct(Product $product, string $locale): ?array
    {
        $product->loadMissing(['sizeChartRows', 'sizeGrid']);

        if ($product->sizeChartRows->isEmpty()) {
            return null;
        }

        $headers = [
            'size' => __('shop.product.size_chart_size', locale: $locale),
            'chest' => __('shop.product.size_chart_chest', locale: $locale),
            'waist' => __('shop.product.size_chart_waist', locale: $locale),
            'hips' => __('shop.product.size_chart_hips', locale: $locale),
            'inseam' => __('shop.product.size_chart_inseam', locale: $locale),
        ];

        $rows = $product->sizeChartRows
            ->map(fn ($row) => [
                'size' => $row->size,
                'chest' => $row->chest,
                'waist' => $row->waist,
                'hips' => $row->hips,
                'inseam' => $row->inseam,
            ])
            ->values()
            ->all();

        return [
            'unit' => $product->sizeGrid?->unit,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
}
