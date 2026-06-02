<?php

namespace App\Support\Shop;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\SizeGridValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductListingFacets
{
    public static function build(ProductListingQuery $listing, string $locale): array
    {
        $brandScope = static::scopedProductIdsSubquery($listing->withoutFilter('brands'));
        $sizeScope = static::scopedProductIdsSubquery($listing->withoutFilter('sizes'));
        $colorScope = static::scopedProductIdsSubquery($listing->withoutFilter('colors'));
        $priceScope = static::scopedProductIdsSubquery(
            $listing->withoutFilter('price_min')->withoutFilter('price_max')
        );

        $brands = Brand::query()
            ->where('is_active', true)
            ->whereHas('products', fn (Builder $q) => $q->whereIn('products.id', $brandScope))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'label' => $brand->translate('name', $locale) ?? $brand->code,
            ]);

        $sizes = SizeGridValue::query()
            ->whereHas('variants', fn (Builder $q) => $q
                ->where('is_active', true)
                ->whereIn('product_id', $sizeScope))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SizeGridValue $size) => [
                'id' => $size->id,
                'label' => $size->display_value ?: $size->value,
            ]);

        $colors = static::buildColorFacets($colorScope, $locale);

        $priceBounds = $listing->baseQuery()
            ->join('product_variants', function ($join) {
                $join->on('product_variants.product_id', '=', 'products.id')
                    ->where('product_variants.is_active', true);
            })
            ->whereIn('products.id', $priceScope)
            ->selectRaw('MIN(product_variants.price) as min_price, MAX(product_variants.price) as max_price')
            ->first();

        return [
            'brands' => $brands->values()->all(),
            'sizes' => $sizes->values()->all(),
            'colors' => $colors,
            'price_min' => $priceBounds?->min_price ? (float) $priceBounds->min_price : null,
            'price_max' => $priceBounds?->max_price ? (float) $priceBounds->max_price : null,
        ];
    }

    /**
     * @return list<array{id: int, label: string, hex: ?string}>
     */
    protected static function buildColorFacets(Builder $productScope, string $locale): array
    {
        $colorAttr = Attribute::query()->where('code', 'color')->first();

        if (! $colorAttr) {
            return [];
        }

        $colorValueIds = DB::table('product_variant_attribute_value')
            ->join('product_variants', 'product_variants.id', '=', 'product_variant_attribute_value.product_variant_id')
            ->join('attribute_values', 'attribute_values.id', '=', 'product_variant_attribute_value.attribute_value_id')
            ->where('product_variants.is_active', true)
            ->where('attribute_values.attribute_id', $colorAttr->id)
            ->whereIn('product_variants.product_id', $productScope)
            ->distinct()
            ->pluck('attribute_values.id');

        $productsWithColor = DB::table('products')
            ->whereIn('id', $productScope)
            ->whereNotNull('color_hex')
            ->where('color_hex', '!=', '')
            ->select('color_hex', 'color_label', 'color_slug')
            ->distinct()
            ->get();

        foreach ($productsWithColor as $row) {
            $value = ProductColorVariantSync::ensureAttributeValue(
                hex: $row->color_hex,
                label: $row->color_label,
                slug: $row->color_slug,
                locale: $locale,
            );

            if ($value) {
                $colorValueIds->push($value->id);
            }
        }

        $colorValueIds = $colorValueIds->unique()->values();

        if ($colorValueIds->isEmpty()) {
            return [];
        }

        return AttributeValue::query()
            ->whereIn('id', $colorValueIds)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (AttributeValue $value) => [
                'id' => $value->id,
                'label' => $value->translate('label', $locale) ?? $value->code,
                'hex' => $value->color_hex,
            ])
            ->values()
            ->all();
    }

    protected static function scopedProductIdsSubquery(ProductListingQuery $listing): Builder
    {
        return $listing->filteredQuery()->select('products.id');
    }
}
