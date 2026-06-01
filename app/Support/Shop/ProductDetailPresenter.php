<?php

namespace App\Support\Shop;

use App\Enums\ProductRelationType;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class ProductDetailPresenter
{
    public static function fromProduct(Product $product, string $locale, ?string $colorCode = null): array
    {
        $product->loadMissing([
            'brand.translates',
            'primaryCategory.translates',
            'categories.translates',
            'images',
            'variants.attributeValues.attribute',
            'variants.sizeGridValue',
            'translates',
            'productRelations.relatedProduct.images',
            'productRelations.relatedProduct.translates',
            'productRelations.relatedProduct.brand.translates',
            'productRelations.relatedProduct.variants',
            'detailItems.translates',
        ]);

        $name = $product->translate('name', $locale) ?? $product->sku;
        $brandName = $product->brand?->translate('name', $locale);
        $slug = $product->translate('slug', $locale) ?? $product->sku;

        $variants = $product->variants
            ->where('is_active', true)
            ->values();

        $variantRows = $variants->map(
            fn (ProductVariant $variant) => static::mapVariant($variant, $locale),
        );

        $colorOptions = static::buildColorOptions($variants, $locale);
        $selectedColor = $colorCode
            ? $colorOptions->first(fn (array $c) => $c['code'] === $colorCode)
            : null;
        $selectedColor ??= $colorOptions->first();

        $sizesForColor = static::sizesForColor($variantRows, $selectedColor['id'] ?? null);

        $defaultVariant = $variantRows->firstWhere('is_default', true)
            ?? $variantRows->firstWhere('color_id', $selectedColor['id'] ?? null)
            ?? $variantRows->first();

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'slug' => $slug,
            'name' => $name,
            'brand' => $brandName,
            'display_name' => $brandName && ! str_starts_with($name, $brandName)
                ? "{$brandName} {$name}"
                : $name,
            'short_description' => $product->translate('short_description', $locale),
            'description' => $product->translate('description', $locale),
            'fit_description' => $product->translate('fit_description', $locale),
            'fabric_description' => $product->translate('fabric_description', $locale),
            'is_new' => (bool) $product->is_new,
            'is_ready_to_ship' => (bool) $product->is_ready_to_ship,
            'category' => $product->primaryCategory?->translate('name', $locale),
            'category_slug' => $product->primaryCategory?->translate('slug', $locale),
            'images' => $product->images->isNotEmpty()
                ? $product->images->map(fn ($img) => [
                    'url' => MediaUrl::fromPath($img->path, $img->disk) ?? asset('images/products/placeholder.jpg'),
                    'alt' => $img->alt ?: $name,
                ])->all()
                : [['url' => asset('images/products/placeholder.jpg'), 'alt' => $name]],
            'colors' => $colorOptions->values()->all(),
            'sizes' => $sizesForColor,
            'variants' => $variantRows->values()->all(),
            'selected_color_id' => $selectedColor['id'] ?? null,
            'default_variant_id' => $defaultVariant['id'] ?? null,
            'url' => route('product.show', ['locale' => $locale, 'product' => $slug]),
            'consultation_url' => route('consultation.show', [
                'locale' => $locale,
                'product' => $slug,
            ]),
            'related' => static::buildRelatedGroups($product, $locale),
            'detail_items' => static::mapDetailItems($product, $locale),
            'similar_products' => static::resolveSimilarProducts($product, $locale),
        ];
    }

    /**
     * @return list<array{label: ?string, description: ?string}>
     */
    protected static function mapDetailItems(Product $product, string $locale): array
    {
        return $product->detailItems
            ->map(fn ($item) => [
                'label' => $item->translate('label', $locale),
                'description' => $item->translate('description', $locale),
            ])
            ->filter(fn (array $row) => filled($row['label']) || filled($row['description']))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function resolveSimilarProducts(Product $product, string $locale, int $limit = 8): array
    {
        $cards = [];

        foreach ($product->productRelations->sortBy('sort_order') as $relation) {
            if ($relation->type !== ProductRelationType::Similar) {
                continue;
            }

            $related = $relation->relatedProduct;

            if (! $related || $related->status !== 'published') {
                continue;
            }

            $cards[] = ProductCardPresenter::fromProduct($related, $locale);

            if (count($cards) >= $limit) {
                return $cards;
            }
        }

        if (count($cards) >= $limit) {
            return $cards;
        }

        $excludeIds = array_merge(
            [$product->id],
            array_map(fn (array $card) => $card['product_id'], $cards),
        );

        $random = Product::query()
            ->published()
            ->with([
                'brand.translates',
                'primaryCategory.translates',
                'images',
                'variants.attributeValues',
                'translates',
            ])
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->limit($limit - count($cards))
            ->get();

        return array_merge(
            $cards,
            ProductCardPresenter::collection($random, $locale)->all(),
        );
    }

    protected static function buildRelatedGroups(Product $product, string $locale): array
    {
        $groups = [
            'color_variants' => [],
            'alternatives' => [],
            'similar' => [],
        ];

        foreach ($product->productRelations as $relation) {
            $related = $relation->relatedProduct;

            if (! $related || $related->status !== 'published') {
                continue;
            }

            $key = match ($relation->type) {
                ProductRelationType::ColorVariant => 'color_variants',
                ProductRelationType::Alternative => 'alternatives',
                ProductRelationType::Similar => 'similar',
            };

            $groups[$key][] = ProductCardPresenter::fromProduct($related, $locale);
        }

        return $groups;
    }

    protected static function mapVariant(ProductVariant $variant, string $locale): array
    {
        $color = $variant->attributeValues->first(fn ($v) => $v->color_hex);

        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => (float) $variant->price,
            'price_formatted' => ProductCardPresenter::formatPrice($variant->price),
            'compare_at_price' => $variant->compare_at_price ? (float) $variant->compare_at_price : null,
            'compare_at_formatted' => $variant->compare_at_price
                ? ProductCardPresenter::formatPrice($variant->compare_at_price)
                : null,
            'size' => $variant->sizeGridValue?->display_value ?? $variant->sizeGridValue?->value,
            'size_value' => $variant->sizeGridValue?->value,
            'color_id' => $color?->id,
            'color_code' => $color?->code,
            'color_label' => $color?->translate('label', $locale) ?? $color?->code,
            'color_hex' => $color?->color_hex,
            'is_default' => $variant->is_default,
        ];
    }

    protected static function buildColorOptions(Collection $variants, string $locale): Collection
    {
        return $variants
            ->flatMap(fn (ProductVariant $variant) => $variant->attributeValues->filter(fn ($v) => $v->color_hex))
            ->unique('id')
            ->map(fn ($value) => [
                'id' => $value->id,
                'code' => $value->code,
                'label' => $value->translate('label', $locale) ?? $value->code,
                'hex' => $value->color_hex,
            ])
            ->values();
    }

    protected static function sizesForColor(Collection $variantRows, ?int $colorId): array
    {
        $filtered = $colorId
            ? $variantRows->where('color_id', $colorId)
            : $variantRows;

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
