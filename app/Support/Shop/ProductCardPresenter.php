<?php

namespace App\Support\Shop;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductCardPresenter
{
    public static function fromProduct(Product $product, string $locale, bool $compact = false): array
    {
        $product->loadMissing([
            'brand.translates',
            'primaryCategory.translates',
            'images',
            'variants.attributeValues.attribute',
        ]);

        $name = $product->translate('name', $locale);
        $brandName = $product->brand?->translate('name', $locale);

        if ($compact) {
            $displayName = $name ?? $product->sku;
        } elseif ($brandName && $name && ! str_starts_with($name, $brandName)) {
            $displayName = "{$brandName} {$name}";
        } else {
            $displayName = $name ?? $product->sku;
        }

        $price = $product->variants->min('price') ?? $product->base_price;
        $slug = $product->translate('slug', $locale);

        return [
            'url' => $slug
                ? route('product.show', ['locale' => $locale, 'product' => $slug])
                : '#',
            'name' => $displayName,
            'category' => $compact ? null : $product->primaryCategory?->translate('name', $locale),
            'price' => $price,
            'price_formatted' => static::formatPrice($price),
            'image' => static::resolveImage($product),
            'colors' => $compact ? [] : static::resolveColors($product),
            'alt' => $displayName,
            'is_new' => (bool) $product->is_new,
        ];
    }

    public static function collection(Collection $products, string $locale, bool $compact = false): Collection
    {
        return $products->map(fn (Product $product) => static::fromProduct($product, $locale, $compact));
    }

    public static function formatPrice(mixed $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        $value = (float) $amount;

        return number_format($value, 0, ',', ' ').' '.config('shop.currency_symbol', 'zł');
    }

    protected static function resolveImage(Product $product): string
    {
        $image = $product->images->firstWhere('is_primary', true)
            ?? $product->images->first();

        if ($image?->path) {
            if (str_starts_with($image->path, 'http://')
                || str_starts_with($image->path, 'https://')
                || str_starts_with($image->path, '/')
                || str_starts_with($image->path, 'images/')) {
                return asset($image->path);
            }

            return $image->disk === 'public'
                ? asset('storage/'.$image->path)
                : asset($image->path);
        }

        return asset('images/products/placeholder.jpg');
    }

    protected static function resolveColors(Product $product): array
    {
        return $product->variants
            ->flatMap(fn ($variant) => $variant->attributeValues)
            ->filter(fn ($value) => $value->color_hex)
            ->unique('id')
            ->take(6)
            ->pluck('color_hex')
            ->values()
            ->all();
    }
}
