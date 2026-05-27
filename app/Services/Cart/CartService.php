<?php

namespace App\Services\Cart;

use App\Enums\ShopEventType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\Analytics\ShopAnalyticsService;
use App\Support\Shop\CartPresenter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function resolve(): Cart
    {
        $token = session('cart_token');

        if (! $token) {
            $token = (string) Str::uuid();
            session(['cart_token' => $token]);
        }

        return Cart::query()->firstOrCreate(['token' => $token]);
    }

    public function add(int $variantId, int $quantity = 1): array
    {
        $variant = ProductVariant::query()
            ->where('is_active', true)
            ->with('product')
            ->findOrFail($variantId);

        if ($variant->product?->status !== 'published') {
            throw ValidationException::withMessages([
                'variant_id' => [__('shop.cart.unavailable')],
            ]);
        }

        $cart = $this->resolve();
        $item = CartItem::query()->firstOrNew([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        $newQty = ($item->exists ? $item->quantity : 0) + $quantity;

        $item->quantity = $newQty;
        $item->save();

        app(ShopAnalyticsService::class)->track(
            ShopEventType::AddToCart,
            $variant->product_id,
            variantId: $variant->id,
            payload: ['quantity' => $newQty],
        );

        return $this->present();
    }

    public function update(int $itemId, int $quantity): array
    {
        $cart = $this->resolve();
        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->with('variant.product')
            ->findOrFail($itemId);

        if ($quantity < 1) {
            $variant = $item->variant;
            $item->delete();

            if ($variant?->product_id) {
                app(ShopAnalyticsService::class)->track(
                    ShopEventType::RemoveFromCart,
                    $variant->product_id,
                    variantId: $variant->id,
                );
            }

            return $this->present();
        }

        $item->update(['quantity' => $quantity]);

        return $this->present();
    }

    public function remove(int $itemId): array
    {
        $cart = $this->resolve();
        CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->delete();

        return $this->present();
    }

    public function clear(): array
    {
        $this->resolve()->items()->delete();

        return $this->present();
    }

    public function count(): int
    {
        return (int) $this->resolve()
            ->items()
            ->sum('quantity');
    }

    public function present(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $cart = $this->resolve()->load([
            'items.variant.product.brand.translates',
            'items.variant.product.images',
            'items.variant.product.translates',
            'items.variant.attributeValues',
            'items.variant.sizeGridValue',
        ]);

        return CartPresenter::fromCart($cart, $locale);
    }
}
