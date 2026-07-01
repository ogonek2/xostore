<?php

namespace App\Support\Shop;

use App\Models\Product;
use App\Services\Promotion\PromotionDiscountService;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class ProductCardPresenter
{
    public static function fromProduct(Product $product, string $locale, bool $compact = false): array
    {
        $product->loadMissing([
            'brand.translates',
            'primaryCategory.translates',
            'images',
            'color',
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

        $basePrice = $product->variants->min('price') ?? $product->base_price;
        $discounts = app(PromotionDiscountService::class);
        $price = $discounts->applyDiscount((float) $basePrice, $product);
        $compareAt = $price < (float) $basePrice ? (float) $basePrice : null;
        $slug = $product->translate('slug', $locale) ?? $product->sku;
        $defaultVariant = $product->variants
            ->where('is_active', true)
            ->sortByDesc('is_default')
            ->first();

        return [
            'product_id' => $product->id,
            'url' => route('product.show', ['locale' => $locale, 'product' => $slug]),
            'name' => $displayName,
            'category' => $compact ? null : $product->primaryCategory?->translate('name', $locale),
            'price' => $price,
            'price_formatted' => static::formatPrice($price),
            'compare_at_price' => $compareAt,
            'compare_at_formatted' => $compareAt ? static::formatPrice($compareAt) : null,
            'image' => static::resolveImage($product),
            'colors' => $compact ? [] : static::resolveColors($product),
            'alt' => $displayName,
            'is_new' => (bool) $product->is_new,
            'default_variant_id' => $defaultVariant?->id,
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

    public static function resolveImage(Product $product): string
    {
        $image = $product->images->firstWhere('is_primary', true)
            ?? $product->images->first();

        if ($image?->path) {
            return MediaUrl::orPlaceholderSized(
                $image->path,
                $image->disk,
                'images/products/placeholder.jpg',
                (int) config('shop.media.card_width', 640),
            );
        }

        return asset('images/products/placeholder.jpg');
    }

    protected static function resolveColors(Product $product): array
    {
        $colors = collect();

        if ($hex = ProductColorService::resolveHex(
            $product->color_hex,
            $product->color_id,
            $product->color_slug,
        )) {
            $colors->push($hex);
        }

        foreach ($product->variants as $variant) {
            foreach ($variant->attributeValues as $value) {
                if ($hex = ProductColorService::resolveHex($value->color_hex, colorCode: $value->code)) {
                    $colors->push($hex);
                }
            }
        }

        return $colors
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }
}
