<?php

namespace App\Support\Shop;

use App\Enums\ProductRelationType;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

final class ProductColorPresenter
{
    /**
     * @return list<array{id: int, code: string, label: string, hex: ?string}>
     */
    public static function variantColors(Product $product, Collection $variants, string $locale): array
    {
        $colors = collect();

        $productHex = ProductColorService::resolveHex(
            $product->color_hex,
            $product->color_id,
            $product->color_slug,
        );

        if ($productHex) {
            $colors->push([
                'id' => 0,
                'code' => $product->color_slug ?: 'default',
                'label' => $product->color_label ?: ($product->translate('name', $locale) ?? $product->sku),
                'hex' => $productHex,
            ]);
        }

        foreach ($variants as $variant) {
            $color = $variant->attributeValues->first(fn ($v) => filled($v->color_hex));

            if (! $color) {
                continue;
            }

            $colors->push([
                'id' => (int) $color->id,
                'code' => $color->code,
                'label' => $color->translate('label', $locale) ?? $color->code,
                'hex' => ProductColorService::resolveHex($color->color_hex, colorCode: $color->code),
            ]);
        }

        return $colors
            ->unique(fn (array $c) => ($c['hex'] ?? '').'|'.$c['code'])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, label: string, hex: ?string, url: string, slug: string, is_current: bool}>
     */
    public static function colorProducts(Product $product, string $locale): array
    {
        $items = collect();

        $items->push(static::colorProductCard($product, $locale, true));

        foreach ($product->productRelations as $relation) {
            if ($relation->type !== ProductRelationType::ColorVariant) {
                continue;
            }

            $related = $relation->relatedProduct;

            if (! $related || $related->status !== 'published') {
                continue;
            }

            $items->push(static::colorProductCard($related, $locale, false));
        }

        if (filled($product->model_slug)) {
            $siblings = Product::query()
                ->published()
                ->where('model_slug', $product->model_slug)
                ->whereKeyNot($product->id)
                ->with('translates')
                ->orderBy('sort_order')
                ->get();

            foreach ($siblings as $sibling) {
                $items->push(static::colorProductCard($sibling, $locale, false));
            }
        }

        return $items
            ->unique('id')
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, label: string, hex: ?string, url: string, slug: string, is_current: bool}
     */
    protected static function colorProductCard(Product $product, string $locale, bool $isCurrent): array
    {
        $slug = $product->translate('slug', $locale) ?? $product->sku;

        return [
            'id' => $product->id,
            'label' => $product->color_label ?: ($product->translate('name', $locale) ?? $product->sku),
            'hex' => ProductColorService::resolveHex(
                $product->color_hex,
                $product->color_id,
                $product->color_slug,
            ),
            'slug' => $slug,
            'url' => route('product.show', [
                'locale' => $locale,
                'product' => $slug,
                'color' => $product->color_slug,
            ]),
            'is_current' => $isCurrent,
        ];
    }

    public static function sizesForColor(Collection $variantRows, ?int $colorId, bool $hasProductColor): array
    {
        $filtered = ($colorId === 0 || $colorId === null) && $hasProductColor
            ? $variantRows
            : ($colorId
                ? $variantRows->where('color_id', $colorId)
                : $variantRows);

        return $filtered
            ->map(fn (array $variant) => [
                'variant_id' => $variant['id'],
                'label' => $variant['size'] ?? $variant['sku'],
                'value' => $variant['size_value'] ?? $variant['sku'],
            ])
            ->unique('variant_id')
            ->values()
            ->all();
    }
}
