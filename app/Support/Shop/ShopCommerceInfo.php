<?php

namespace App\Support\Shop;

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShopCommerceSettings;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Facades\Cache;

final class ShopCommerceInfo
{
    /**
     * @return array{
     *     delivery: ?array{title: ?string, summary: ?string, details: ?string, lines: list<string>},
     *     payment: ?array{title: ?string, summary: ?string, methods: list<string>},
     *     shipping: ?array{cost: ?float, cost_formatted: ?string, free_from: ?float, free_from_formatted: ?string, line: ?string}
     * }
     */
    public static function forLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return Cache::remember(
            'shop.commerce_info.'.$locale,
            now()->addMinutes(10),
            fn (): array => static::build($locale),
        );
    }

    public static function forgetCache(): void
    {
        foreach (['pl', 'en'] as $locale) {
            Cache::forget('shop.commerce_info.'.$locale);
        }
    }

    /**
     * @return list<array{url: string, thumb_url: string, alt: string}>
     */
    public static function mapReviewImages(Product $product, int $limit = 12): array
    {
        $product->loadMissing('reviewImages');

        return $product->reviewImages
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->take($limit)
            ->values()
            ->map(fn ($image) => [
                'url' => MediaUrl::sized(
                    $image->path,
                    $image->disk,
                    (int) config('shop.media.gallery_width', 1280),
                ) ?? asset('images/products/placeholder.jpg'),
                'thumb_url' => MediaUrl::sized(
                    $image->path,
                    $image->disk,
                    (int) config('shop.media.thumb_width', 160),
                ) ?? asset('images/products/placeholder.jpg'),
                'alt' => $image->alt ?: __('shop.product.reviews_alt'),
            ])
            ->all();
    }

    public static function whatsappUrl(?string $message = null): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) config('shop.whatsapp.phone', '')) ?: null;

        if (! $phone) {
            return null;
        }

        $query = [];

        if (filled($message)) {
            $query['text'] = $message;
        }

        $url = 'https://wa.me/'.$phone;

        return $query === [] ? $url : $url.'?'.http_build_query($query);
    }

    public static function whatsappMessageForProduct(Product|array $product, string $locale): string
    {
        $template = (string) config(
            'shop.whatsapp.message_template',
            'Hello! I am interested in: {product_name} ({product_url})',
        );

        if (is_array($product)) {
            $name = (string) ($product['display_name'] ?? $product['name'] ?? '');
            $url = (string) ($product['url'] ?? '');
        } else {
            $name = $product->translate('name', $locale) ?? $product->sku;
            $brand = $product->brand?->translate('name', $locale);
            if ($brand && $name && ! str_starts_with($name, $brand)) {
                $name = "{$brand} {$name}";
            }
            $slug = $product->translate('slug', $locale) ?? $product->sku;
            $url = route('product.show', ['locale' => $locale, 'product' => $slug]);
        }

        return strtr($template, [
            '{product_name}' => $name,
            '{product_url}' => $url,
        ]);
    }

    /**
     * @return array{
     *     delivery: ?array{title: ?string, summary: ?string, details: ?string, lines: list<string>},
     *     payment: ?array{title: ?string, summary: ?string, methods: list<string>},
     *     shipping: ?array{cost: ?float, cost_formatted: ?string, free_from: ?float, free_from_formatted: ?string, line: ?string}
     * }
     */
    private static function build(string $locale): array
    {
        $settings = ShopCommerceSettings::instance();
        $methods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $methodLabels = $methods
            ->map(fn (PaymentMethod $method) => $method->label($locale))
            ->filter()
            ->values()
            ->all();

        $shippingMethod = $methods->first(fn (PaymentMethod $method) => $method->shipping_enabled);
        $shipping = null;

        if ($shippingMethod) {
            $cost = $shippingMethod->shipping_cost !== null ? (float) $shippingMethod->shipping_cost : null;
            $freeFrom = $shippingMethod->free_shipping_enabled && $shippingMethod->free_shipping_from !== null
                ? (float) $shippingMethod->free_shipping_from
                : null;

            $lineParts = [];

            if ($cost !== null) {
                $lineParts[] = __('shop.product.shipping_from', [
                    'price' => ProductCardPresenter::formatPrice($cost),
                ], $locale);
            }

            if ($freeFrom !== null) {
                $lineParts[] = __('shop.product.free_shipping_from', [
                    'price' => ProductCardPresenter::formatPrice($freeFrom),
                ], $locale);
            }

            $shipping = [
                'cost' => $cost,
                'cost_formatted' => $cost !== null ? ProductCardPresenter::formatPrice($cost) : null,
                'free_from' => $freeFrom,
                'free_from_formatted' => $freeFrom !== null ? ProductCardPresenter::formatPrice($freeFrom) : null,
                'line' => $lineParts !== [] ? implode(' · ', $lineParts) : null,
            ];
        }

        $delivery = null;

        if ($settings->delivery_enabled) {
            $details = $settings->text('delivery', 'details', $locale);
            $lines = $details
                ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $details) ?: [])))
                : [];

            if (! empty($shipping['line'])) {
                $lines[] = $shipping['line'];
            }

            $delivery = [
                'title' => $settings->text('delivery', 'title', $locale) ?: __('shop.product.delivery_title', locale: $locale),
                'summary' => $settings->text('delivery', 'summary', $locale),
                'details' => $details,
                'lines' => array_values(array_unique($lines)),
            ];
        }

        $payment = null;

        if ($settings->payment_enabled) {
            $payment = [
                'title' => $settings->text('payment', 'title', $locale) ?: __('shop.product.payment_title', locale: $locale),
                'summary' => $settings->text('payment', 'summary', $locale),
                'methods' => $methodLabels,
            ];
        }

        return [
            'delivery' => $delivery,
            'payment' => $payment,
            'shipping' => $shipping,
        ];
    }
}
