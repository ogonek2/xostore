<?php

namespace App\Support\Shop;

use App\Enums\PromotionLayout;
use App\Models\Promotion;

class HomepagePromotions
{
    public static function forHomepage(string $locale): array
    {
        $promotions = Promotion::query()
            ->with('category.translates')
            ->active()
            ->onHomepage()
            ->currentlyRunning()
            ->orderBy('sort_order')
            ->get();

        $presented = PromotionPresenter::collection($promotions, $locale);

        $featured = $presented->first(
            fn (array $card) => $card['layout'] === PromotionLayout::Featured->value
        );

        $compact = $presented->filter(
            fn (array $card) => $card['layout'] === PromotionLayout::Compact->value
        )->values();

        return [
            'featured' => $featured,
            'compact' => $compact,
            'view_all_url' => route('catalog.show', [
                'locale' => $locale,
                'catalog' => $locale === 'en' ? 'promotions' : 'promocje',
            ]),
        ];
    }
}
