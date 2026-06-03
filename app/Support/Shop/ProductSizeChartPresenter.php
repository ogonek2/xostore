<?php

namespace App\Support\Shop;

use App\Models\Product;
use App\Models\ProductSizeChartRow;
use App\Models\SizeChartPreset;
use App\Models\SizeChartPresetRow;
use Illuminate\Support\Collection;

final class ProductSizeChartPresenter
{
    /**
     * @return array{unit: ?string, headers: array<string, string>, rows: list<array<string, ?string>>}|null
     */
    public static function forProduct(Product $product, string $locale): ?array
    {
        $product->loadMissing([
            'sizeChartRows',
            'sizeChartPreset.rows',
        ]);

        $headers = [
            'size' => __('shop.product.size_chart_size', locale: $locale),
            'chest' => __('shop.product.size_chart_chest', locale: $locale),
            'waist' => __('shop.product.size_chart_waist', locale: $locale),
            'hips' => __('shop.product.size_chart_hips', locale: $locale),
            'inseam' => __('shop.product.size_chart_inseam', locale: $locale),
        ];

        if ($product->sizeChartRows->isNotEmpty()) {
            return [
                'unit' => $product->sizeChartPreset?->unit ?? 'cm',
                'headers' => $headers,
                'rows' => static::mapProductRows($product->sizeChartRows),
            ];
        }

        $preset = $product->sizeChartPreset;

        if (! $preset || $preset->rows->isEmpty()) {
            return null;
        }

        return [
            'unit' => $preset->unit ?: 'cm',
            'headers' => $headers,
            'rows' => static::mapPresetRows($preset->rows, $preset->unit ?: 'cm'),
        ];
    }

    /**
     * @param  Collection<int, ProductSizeChartRow>  $rows
     * @return list<array<string, ?string>>
     */
    protected static function mapProductRows(Collection $rows): array
    {
        return $rows
            ->map(fn (ProductSizeChartRow $row) => [
                'size' => $row->size,
                'chest' => $row->chest,
                'waist' => $row->waist,
                'hips' => $row->hips,
                'inseam' => $row->inseam,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, SizeChartPresetRow>  $rows
     * @return list<array<string, ?string>>
     */
    protected static function mapPresetRows(Collection $rows, string $unit): array
    {
        return $rows
            ->map(fn (SizeChartPresetRow $row) => [
                'size' => $row->size,
                'chest' => SizeChartMeasurementFormatter::format($row->chest_cm, $unit),
                'waist' => SizeChartMeasurementFormatter::format($row->waist_cm, $unit),
                'hips' => SizeChartMeasurementFormatter::format($row->hips_cm, $unit),
                'inseam' => SizeChartMeasurementFormatter::format($row->inseam_cm, $unit),
            ])
            ->values()
            ->all();
    }
}
