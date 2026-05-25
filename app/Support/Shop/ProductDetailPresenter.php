<?php

namespace App\Support\Shop;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductDetailPresenter
{
    public static function fromProduct(Product $product, string $locale): array
    {
        $product->loadMissing([
            'brand.translates',
            'primaryCategory.translates',
            'categories.translates',
            'images',
            'variants.attributeValues.attribute',
            'variants.sizeGridValue',
            'translates',
        ]);

        $name = $product->translate('name', $locale) ?? $product->sku;
        $brandName = $product->brand?->translate('name', $locale);
        $slug = $product->translate('slug', $locale) ?? $product->sku;

        $variants = $product->variants
            ->where('is_active', true)
            ->values()
            ->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'price_formatted' => ProductCardPresenter::formatPrice($variant->price),
                'compare_at_price' => $variant->compare_at_price ? (float) $variant->compare_at_price : null,
                'compare_at_formatted' => $variant->compare_at_price
                    ? ProductCardPresenter::formatPrice($variant->compare_at_price)
                    : null,
                'stock_qty' => $variant->stock_qty,
                'size' => $variant->sizeGridValue?->display_value ?? $variant->sizeGridValue?->value,
                'colors' => $variant->attributeValues
                    ->filter(fn ($v) => $v->color_hex)
                    ->map(fn ($v) => [
                        'id' => $v->id,
                        'hex' => $v->color_hex,
                        'label' => $v->translate('label', $locale) ?? $v->code,
                    ])
                    ->values()
                    ->all(),
                'is_default' => $variant->is_default,
            ])
            ->all();

        $defaultVariant = collect($variants)->firstWhere('is_default', true) ?? ($variants[0] ?? null);

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
            'is_new' => (bool) $product->is_new,
            'category' => $product->primaryCategory?->translate('name', $locale),
            'category_slug' => $product->primaryCategory?->translate('slug', $locale),
            'images' => $product->images->isNotEmpty()
                ? $product->images->map(fn ($img) => [
                    'url' => static::imageUrl($img->path, $img->disk),
                    'alt' => $img->alt ?: $name,
                ])->all()
                : [['url' => asset('images/products/placeholder.jpg'), 'alt' => $name]],
            'variants' => $variants,
            'default_variant' => $defaultVariant,
            'url' => route('product.show', ['locale' => $locale, 'product' => $slug]),
        ];
    }

    protected static function imageUrl(string $path, string $disk = 'public'): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return $disk === 'public' ? asset('storage/'.$path) : asset($path);
    }
}
