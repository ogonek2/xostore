<?php

namespace App\Support\Shop;

use App\Enums\PromotionLayout;
use App\Enums\PromotionProductTargetType;
use App\Models\Promotion;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class PromotionPresenter
{
    public static function fromPromotion(Promotion $promotion, string $locale): array
    {
        $promotion->loadMissing([
            'category.translates',
            'catalog.translates',
            'brand.translates',
        ]);

        return [
            'layout' => $promotion->layout instanceof PromotionLayout
                ? $promotion->layout->value
                : $promotion->layout,
            'title' => $promotion->translate('title', $locale),
            'subtitle' => $promotion->translate('subtitle', $locale),
            'cta_label' => $promotion->translate('cta_label', $locale) ?: __('shop.promotions.cta'),
            'url' => static::resolveUrl($promotion, $locale),
            'image' => static::resolveImage($promotion),
            'expires_at' => $promotion->expires_at,
            'discount_percent' => $promotion->discount_percent,
        ];
    }

    public static function collection(Collection $promotions, string $locale): Collection
    {
        return $promotions->map(fn (Promotion $promotion) => static::fromPromotion($promotion, $locale));
    }

    protected static function resolveUrl(Promotion $promotion, string $locale): string
    {
        if ($promotion->link_url) {
            return $promotion->link_url;
        }

        $target = $promotion->product_target_type;

        if ($target === PromotionProductTargetType::Catalog && $promotion->catalog) {
            $slug = $promotion->catalog->translate('slug', $locale) ?? $promotion->catalog->code;

            return route('catalog.show', ['locale' => $locale, 'catalog' => $slug]);
        }

        if ($target === PromotionProductTargetType::Category && $promotion->category) {
            $slug = $promotion->category->translate('slug', $locale) ?? $promotion->category->code;

            return route('category.show', ['locale' => $locale, 'category' => $slug]);
        }

        if ($target === PromotionProductTargetType::Brand && $promotion->brand) {
            return route('products.index', [
                'locale' => $locale,
                'brands' => [$promotion->brand->id],
            ]);
        }

        if ($target === PromotionProductTargetType::Products) {
            return route('promotions.index', ['locale' => $locale]);
        }

        $slug = $promotion->category?->translate('slug', $locale);

        if ($slug) {
            return route('category.show', ['locale' => $locale, 'category' => $slug]);
        }

        return route('promotions.index', ['locale' => $locale]);
    }

    protected static function resolveImage(Promotion $promotion): ?string
    {
        if (! $promotion->image_path) {
            return null;
        }

        return MediaUrl::fromPath($promotion->image_path);
    }
}
