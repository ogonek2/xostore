<?php

namespace App\Support\Shop;

use App\Enums\PromotionLayout;
use App\Models\Promotion;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class PromotionPresenter
{
    public static function fromPromotion(Promotion $promotion, string $locale): array
    {
        $promotion->loadMissing('category.translates');

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

        $slug = $promotion->category?->translate('slug', $locale);

        if ($slug) {
            return url("/{$locale}/c/{$slug}");
        }

        return '#';
    }

    protected static function resolveImage(Promotion $promotion): ?string
    {
        if (! $promotion->image_path) {
            return null;
        }

        return MediaUrl::fromPath($promotion->image_path);
    }
}
