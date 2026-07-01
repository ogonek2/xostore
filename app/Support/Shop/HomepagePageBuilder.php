<?php

namespace App\Support\Shop;

use App\Enums\CatalogHomepageSection;
use App\Enums\HomepageBlockType;
use App\Models\Catalog;
use App\Models\HomepageSettings;

final class HomepagePageBuilder
{
    /**
     * @return list<array{type: string, props: array<string, mixed>}>
     */
    public static function resolve(string $locale, ?HomepageSettings $settings = null): array
    {
        $settings ??= HomepageSettings::instance();
        $resolved = [];

        foreach ($settings->resolvedBlocks() as $block) {
            if (! ($block['is_active'] ?? true)) {
                continue;
            }

            $type = HomepageBlockType::tryFrom((string) ($block['type'] ?? ''));

            if (! $type) {
                continue;
            }

            $payload = static::resolveBlock($type, $block, $locale);

            if ($payload !== null) {
                $resolved[] = $payload;
            }
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array{type: string, props: array<string, mixed>}|null
     */
    protected static function resolveBlock(HomepageBlockType $type, array $block, string $locale): ?array
    {
        $settings = is_array($block['settings'] ?? null) ? $block['settings'] : [];
        $title = static::localizedTitle($settings, $locale);

        return match ($type) {
            HomepageBlockType::Hero => static::resolveHero(),
            HomepageBlockType::Banners => static::resolveBanners(),
            HomepageBlockType::CategoryShowcase => static::resolveCategoryShowcase($settings, $locale, $title),
            HomepageBlockType::Trending => static::resolveTrending($locale, $title),
            HomepageBlockType::Promotions => static::resolvePromotions($locale, $title),
            HomepageBlockType::NewArrivals => static::resolveNewArrivals($locale, $title),
            HomepageBlockType::Catalog => static::resolveCatalog($settings, $locale, $title),
            HomepageBlockType::Spacer => static::resolveSpacer($settings),
        };
    }

    /**
     * @return array{type: string, props: array<string, mixed>}|null
     */
    protected static function resolveHero(): ?array
    {
        $sections = HomepageHeroBanners::sections();

        if ($sections === []) {
            return null;
        }

        return [
            'type' => HomepageBlockType::Hero->value,
            'props' => ['sections' => $sections],
        ];
    }

    /**
     * @return array{type: string, props: array<string, mixed>}|null
     */
    protected static function resolveBanners(): ?array
    {
        if (! (bool) config('shop.homepage_banners.enabled', true)) {
            return null;
        }

        $items = HomepageBanners::items();

        if ($items->isEmpty()) {
            return null;
        }

        return [
            'type' => HomepageBlockType::Banners->value,
            'props' => ['items' => $items],
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array{type: string, props: array<string, mixed>}|null
     */
    protected static function resolveCategoryShowcase(array $settings, string $locale, ?string $title): ?array
    {
        $items = $settings['items'] ?? null;
        $cards = HomepageCategoryShowcase::cards($locale, is_array($items) ? $items : null);

        if ($cards->isEmpty()) {
            return null;
        }

        return [
            'type' => HomepageBlockType::CategoryShowcase->value,
            'props' => [
                'cards' => $cards,
                'title' => $title,
            ],
        ];
    }

    /**
     * @return array{type: string, props: array<string, mixed>}
     */
    protected static function resolveTrending(string $locale, ?string $title): array
    {
        $products = TrendingProducts::forHomepage($locale);
        $viewMoreUrl = CatalogHomepageProducts::forSection(CatalogHomepageSection::Trending, $locale)['view_all_url'];

        return [
            'type' => HomepageBlockType::Trending->value,
            'props' => [
                'products' => $products,
                'title' => $title,
                'viewMoreUrl' => $viewMoreUrl,
            ],
        ];
    }

    /**
     * @return array{type: string, props: array<string, mixed>}
     */
    protected static function resolvePromotions(string $locale, ?string $title): array
    {
        $promotions = HomepagePromotions::forHomepage($locale);

        return [
            'type' => HomepageBlockType::Promotions->value,
            'props' => [
                'featured' => $promotions['featured'],
                'compact' => $promotions['compact'],
                'viewAllUrl' => $promotions['view_all_url'],
                'viewMoreUrl' => $promotions['view_all_url'],
                'title' => $title,
            ],
        ];
    }

    /**
     * @return array{type: string, props: array<string, mixed>}
     */
    protected static function resolveNewArrivals(string $locale, ?string $title): array
    {
        $newArrivals = NewArrivalsProducts::forHomepage($locale);

        return [
            'type' => HomepageBlockType::NewArrivals->value,
            'props' => [
                'products' => $newArrivals['products'],
                'viewAllUrl' => $newArrivals['view_all_url'],
                'title' => $title,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array{type: string, props: array<string, mixed>}|null
     */
    protected static function resolveCatalog(array $settings, string $locale, ?string $title): ?array
    {
        $catalogId = (int) ($settings['catalog_id'] ?? 0);

        if ($catalogId <= 0) {
            return null;
        }

        $limit = max(4, min(24, (int) ($settings['limit'] ?? 12)));
        $layout = (string) ($settings['layout'] ?? 'trending');
        $result = CatalogHomepageProducts::forCatalog($catalogId, $locale, $limit);

        if ($result['products']->isEmpty()) {
            return null;
        }

        $products = match ($layout) {
            'new_arrivals' => ProductCardPresenter::collection($result['products'], $locale, compact: true),
            default => ProductCardPresenter::collection($result['products'], $locale),
        };

        return [
            'type' => HomepageBlockType::Catalog->value,
            'props' => [
                'products' => $products,
                'layout' => $layout,
                'title' => $title ?? ($result['catalog']?->translate('name', $locale)),
                'viewMoreUrl' => $result['view_all_url'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array{type: string, props: array<string, mixed>}
     */
    protected static function resolveSpacer(array $settings): array
    {
        return [
            'type' => HomepageBlockType::Spacer->value,
            'props' => [
                'size' => (string) ($settings['size'] ?? 'md'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    protected static function localizedTitle(array $settings, string $locale): ?string
    {
        $titles = $settings['title'] ?? null;

        if (! is_array($titles)) {
            return null;
        }

        $title = $titles[$locale] ?? $titles[config('shop.default_language', 'pl')] ?? null;

        return filled($title) ? (string) $title : null;
    }
}
