<?php

namespace App\Support\Shop;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductListingFacets
{
    public static function build(ProductListingQuery $listing, string $locale): array
    {
        $scopedIds = $listing->baseQuery()->select('products.id');

        $brands = Brand::query()
            ->where('is_active', true)
            ->whereHas('products', fn (Builder $q) => $q->whereIn('products.id', $scopedIds))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'label' => $brand->translate('name', $locale) ?? $brand->code,
            ]);

        $colorAttr = Attribute::query()->where('code', 'color')->first();

        $colors = collect();
        if ($colorAttr) {
            $colors = AttributeValue::query()
                ->where('attribute_id', $colorAttr->id)
                ->whereHas('variants.product', fn (Builder $q) => $q->whereIn('products.id', $scopedIds))
                ->orderBy('sort_order')
                ->get()
                ->map(fn (AttributeValue $value) => [
                    'id' => $value->id,
                    'label' => $value->translate('label', $locale) ?? $value->code,
                    'hex' => $value->color_hex,
                ]);
        }

        $priceBounds = $listing->baseQuery()
            ->join('product_variants', function ($join) {
                $join->on('product_variants.product_id', '=', 'products.id')
                    ->where('product_variants.is_active', true);
            })
            ->selectRaw('MIN(product_variants.price) as min_price, MAX(product_variants.price) as max_price')
            ->first();

        return [
            'brands' => $brands->values()->all(),
            'colors' => $colors->values()->all(),
            'price_min' => $priceBounds?->min_price ? (float) $priceBounds->min_price : null,
            'price_max' => $priceBounds?->max_price ? (float) $priceBounds->max_price : null,
        ];
    }
}
