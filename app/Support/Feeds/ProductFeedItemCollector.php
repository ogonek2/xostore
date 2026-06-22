<?php

namespace App\Support\Feeds;

use App\Models\FeedSettings;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Promotion\PromotionDiscountService;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Str;

final class ProductFeedItemCollector
{
    /** @var list<ProductFeedItem> */
    private array $items = [];

    private PromotionDiscountService $discounts;

    public function __construct(
        private readonly FeedSettings $settings,
        ?PromotionDiscountService $discounts = null,
    ) {
        $this->discounts = $discounts ?? app(PromotionDiscountService::class);
    }

    /**
     * @return list<ProductFeedItem>
     */
    public function collect(): array
    {
        $this->items = [];
        $locale = $this->settings->locale ?: (string) config('shop.default_language', 'pl');
        $currency = (string) config('shop.currency', 'PLN');

        Product::query()
            ->published()
            ->with([
                'brand.translates',
                'translates',
                'images',
                'primaryCategory.translates',
                'variants' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'variants.sizeGridValue',
                'variants.attributeValues.attribute',
            ])
            ->orderBy('id')
            ->chunkById(50, function ($products) use ($locale, $currency): void {
                foreach ($products as $product) {
                    $this->collectProduct($product, $locale, $currency);
                }
            });

        return $this->items;
    }

    private function collectProduct(Product $product, string $locale, string $currency): void
    {
        $variants = $product->variants->where('is_active', true)->values();

        if ($variants->isEmpty()) {
            return;
        }

        $imageLink = $this->resolveImageUrl($product);

        if ($imageLink === null) {
            return;
        }

        $baseTitle = $this->productTitle($product, $locale);
        $description = $this->productDescription($product, $locale);
        $brand = $product->brand?->translate('name', $locale) ?? config('shop.name', 'XOStore');
        $link = $this->productUrl($product, $locale);
        $itemGroupId = filled($product->model_slug) ? (string) $product->model_slug : (string) $product->id;
        $category = $this->settings->google_product_category ?: null;
        $condition = $this->settings->product_condition ?: 'new';
        $color = $product->color_label ?: $this->variantColorLabel($variants->first(), $locale);

        if ($variants->count() === 1) {
            $variant = $variants->first();
            $item = $this->mapVariant(
                product: $product,
                variant: $variant,
                locale: $locale,
                currency: $currency,
                itemGroupId: $itemGroupId,
                title: $baseTitle,
                description: $description,
                brand: $brand,
                link: $link,
                imageLink: $imageLink,
                condition: $condition,
                category: $category,
                color: $color,
            );

            if ($item) {
                $this->items[] = $item;
            }

            return;
        }

        foreach ($variants as $variant) {
            $sizeLabel = $variant->sizeGridValue?->display_value
                ?? $variant->sizeGridValue?->value;
            $title = $sizeLabel
                ? $this->truncate($baseTitle.' — '.$sizeLabel, 150)
                : $baseTitle;

            $item = $this->mapVariant(
                product: $product,
                variant: $variant,
                locale: $locale,
                currency: $currency,
                itemGroupId: $itemGroupId,
                title: $title,
                description: $description,
                brand: $brand,
                link: $link,
                imageLink: $imageLink,
                condition: $condition,
                category: $category,
                color: $color,
            );

            if ($item) {
                $this->items[] = $item;
            }
        }
    }

    private function mapVariant(
        Product $product,
        ProductVariant $variant,
        string $locale,
        string $currency,
        string $itemGroupId,
        string $title,
        string $description,
        string $brand,
        string $link,
        string $imageLink,
        string $condition,
        ?string $category,
        ?string $color,
    ): ?ProductFeedItem {
        $availability = $this->availability($product, $variant);

        if ($availability === 'out of stock' && ! $this->settings->include_out_of_stock) {
            return null;
        }

        $basePrice = (float) $variant->price;
        $price = $this->discounts->applyDiscount($basePrice, $product);
        $compareAt = $variant->compare_at_price ? (float) $variant->compare_at_price : null;

        if ($compareAt !== null && $compareAt > $basePrice) {
            $listPrice = $compareAt;
            $salePrice = $price;
        } elseif ($price < $basePrice) {
            $listPrice = $basePrice;
            $salePrice = $price;
        } else {
            $listPrice = $price;
            $salePrice = null;
        }

        if ($listPrice <= 0) {
            return null;
        }

        return new ProductFeedItem(
            id: (string) ($variant->sku ?: $variant->id),
            itemGroupId: $itemGroupId,
            title: $this->truncate($title, 150),
            description: $description,
            link: $link,
            imageLink: $imageLink,
            availability: $availability,
            price: $this->formatMoney($listPrice, $currency),
            salePrice: $salePrice !== null ? $this->formatMoney($salePrice, $currency) : null,
            brand: $brand,
            condition: $condition,
            googleProductCategory: $category,
            gtin: filled($variant->barcode) ? (string) $variant->barcode : null,
            mpn: filled($variant->sku) ? (string) $variant->sku : null,
            size: $variant->sizeGridValue?->display_value ?? $variant->sizeGridValue?->value,
            color: $color,
        );
    }

    private function availability(Product $product, ProductVariant $variant): string
    {
        if (! $product->track_inventory) {
            return 'in stock';
        }

        if ($product->is_ready_to_ship) {
            return 'in stock';
        }

        return $variant->stock_qty > 0 ? 'in stock' : 'out of stock';
    }

    private function productTitle(Product $product, string $locale): string
    {
        $name = $product->translate('name', $locale) ?? $product->sku;
        $brand = $product->brand?->translate('name', $locale);

        if ($brand && $name && ! str_starts_with($name, $brand)) {
            return $this->truncate("{$brand} {$name}", 150);
        }

        return $this->truncate((string) $name, 150);
    }

    private function productDescription(Product $product, string $locale): string
    {
        $text = $product->translate('short_description', $locale)
            ?? $product->translate('description', $locale)
            ?? $product->translate('name', $locale)
            ?? $product->sku;

        $text = trim(strip_tags(html_entity_decode((string) $text)));
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return $this->truncate($text, 5000);
    }

    private function productUrl(Product $product, string $locale): string
    {
        $slug = $product->translate('slug', $locale) ?? $product->sku;

        return route('product.show', ['locale' => $locale, 'product' => $slug]);
    }

    private function resolveImageUrl(Product $product): ?string
    {
        $image = $product->images->firstWhere('is_primary', true)
            ?? $product->images->first();

        if (! $image?->path) {
            return null;
        }

        $url = MediaUrl::sized(
            $image->path,
            $image->disk,
            (int) config('shop.media.gallery_width', 1280),
        );

        if (! $url) {
            return null;
        }

        return $this->absoluteUrl($url);
    }

    private function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    private function variantColorLabel(?ProductVariant $variant, string $locale): ?string
    {
        if (! $variant) {
            return null;
        }

        $color = $variant->attributeValues->first(fn ($value) => $value->attribute?->code === 'color');

        return $color?->translate('label', $locale) ?? $color?->code;
    }

    private function formatMoney(float $amount, string $currency): string
    {
        return number_format($amount, 2, '.', '').' '.$currency;
    }

    private function truncate(string $value, int $limit): string
    {
        return Str::limit($value, $limit, '…');
    }
}
