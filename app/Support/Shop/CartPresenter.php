<?php

namespace App\Support\Shop;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\Promotion\PromotionDiscountService;

class CartPresenter
{
    public static function fromCart(Cart $cart, string $locale): array
    {
        $discounts = app(PromotionDiscountService::class);

        $items = $cart->items->map(function (CartItem $item) use ($locale, $discounts) {
            $variant = $item->variant;
            $product = $variant?->product;

            if (! $variant || ! $product) {
                return null;
            }

            $name = $product->translate('name', $locale) ?? $product->sku;
            $brand = $product->brand?->translate('name', $locale);
            $displayName = $brand && ! str_starts_with($name, $brand)
                ? "{$brand} {$name}"
                : $name;

            $color = $variant->attributeValues->first(fn ($v) => $v->color_hex);
            $size = $variant->sizeGridValue?->display_value ?? $variant->sizeGridValue?->value;
            $variantLabel = collect([
                $color?->translate('label', $locale),
                $size,
            ])->filter()->implode(' · ');

            $slug = $product->translate('slug', $locale);
            $basePrice = (float) $variant->price;
            $unitPrice = $discounts->applyDiscount($basePrice, $product);
            $lineTotal = $unitPrice * $item->quantity;

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'quantity' => $item->quantity,
                'name' => $displayName,
                'sku' => $variant->sku,
                'variant_label' => $variantLabel ?: null,
                'unit_price' => $unitPrice,
                'original_unit_price' => $unitPrice < $basePrice ? $basePrice : null,
                'price_formatted' => ProductCardPresenter::formatPrice($unitPrice),
                'original_price_formatted' => $unitPrice < $basePrice ? ProductCardPresenter::formatPrice($basePrice) : null,
                'line_total' => $lineTotal,
                'line_total_formatted' => ProductCardPresenter::formatPrice($lineTotal),
                'image' => ProductCardPresenter::resolveImage($product),
                'url' => $slug
                    ? route('product.show', ['locale' => $locale, 'product' => $slug])
                    : '#',
                'max_quantity' => 99,
            ];
        })->filter()->values();

        $subtotal = $items->sum('line_total');

        return [
            'items' => $items->all(),
            'count' => $items->sum('quantity'),
            'product_ids' => $items->pluck('product_id')->unique()->values()->all(),
            'subtotal' => $subtotal,
            'subtotal_formatted' => ProductCardPresenter::formatPrice($subtotal),
        ];
    }
}
