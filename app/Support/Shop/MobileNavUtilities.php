<?php

namespace App\Support\Shop;

use Illuminate\Support\Collection;

final class MobileNavUtilities
{
    /** @var list<string> */
    private const CATALOG_PANEL_TYPES = [
        'category',
        'brands',
        'catalog_products',
    ];

    /**
     * Mega items for the mobile catalog accordion (categories, brands, catalog links only).
     *
     * @param  list<array<string, mixed>>  $megaItems
     * @return list<array<string, mixed>>
     */
    public static function catalogMegaItems(array $megaItems): array
    {
        return collect($megaItems)
            ->map(function (array $item) {
                $panels = collect($item['panels'] ?? [])
                    ->filter(fn (array $panel) => in_array($panel['type'] ?? '', self::CATALOG_PANEL_TYPES, true))
                    ->filter(fn (array $panel) => static::panelHasMobileContent($panel))
                    ->values()
                    ->all();

                if ($panels === []) {
                    return null;
                }

                return array_merge($item, ['panels' => $panels]);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Canonical shop links + admin simple nav items (deduped against catalog URLs).
     *
     * @param  list<array<string, mixed>>  $catalogMegaItems
     * @return list<array<string, mixed>>
     */
    public static function utilityLinks(int $cartCount, Collection $simpleNavItems, array $catalogMegaItems): array
    {
        $locale = app()->getLocale();
        $catalogUrls = static::collectUrls($catalogMegaItems);

        $links = [
            [
                'label' => __('shop.search'),
                'action' => 'search',
            ],
            [
                'label' => __('shop.cart.label'),
                'action' => 'cart',
                'badge' => $cartCount,
            ],
            [
                'label' => __('shop.footer.links.promotions'),
                'url' => route('catalog.show', [
                    'locale' => $locale,
                    'catalog' => $locale === 'en' ? 'promotions' : 'promocje',
                ]),
            ],
            [
                'label' => __('shop.footer.links.new_arrivals'),
                'url' => route('catalog.show', [
                    'locale' => $locale,
                    'catalog' => $locale === 'en' ? 'new-in' : 'nowynki',
                ]),
            ],
            [
                'label' => __('shop.footer.links.trending'),
                'url' => route('catalog.show', [
                    'locale' => $locale,
                    'catalog' => $locale === 'en' ? 'trends' : 'trendy',
                ]),
            ],
            [
                'label' => __('shop.nav.shop'),
                'url' => route('products.index', ['locale' => $locale]),
            ],
            [
                'label' => __('shop.footer.links.contact'),
                'url' => route('consultation.show', ['locale' => $locale]),
            ],
        ];

        foreach ($simpleNavItems as $item) {
            $url = $item['url'] ?? null;

            if (! $url || static::urlIsListed($url, $catalogUrls)) {
                continue;
            }

            $links[] = [
                'label' => $item['label'],
                'url' => $url,
                'open_in_new_tab' => (bool) ($item['open_in_new_tab'] ?? false),
            ];

            $catalogUrls[] = $url;
        }

        return collect($links)
            ->unique(fn (array $link) => $link['action'] ?? $link['url'] ?? $link['label'])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $megaItems
     * @return list<string>
     */
    public static function collectUrls(array $megaItems): array
    {
        $urls = [];

        foreach ($megaItems as $item) {
            if (! empty($item['url'])) {
                $urls[] = $item['url'];
            }

            foreach ($item['panels'] ?? [] as $panel) {
                if (! empty($panel['view_all_url'])) {
                    $urls[] = $panel['view_all_url'];
                }

                foreach ($panel['links'] ?? [] as $link) {
                    if (! empty($link['url'])) {
                        $urls[] = $link['url'];
                    }
                }
            }
        }

        return array_values(array_unique($urls));
    }

    protected static function panelHasMobileContent(array $panel): bool
    {
        if (! empty($panel['links'])) {
            return true;
        }

        return ($panel['type'] ?? '') === 'catalog_products' && ! empty($panel['view_all_url']);
    }

    protected static function urlIsListed(string $url, array $listed): bool
    {
        $normalized = rtrim($url, '/');

        foreach ($listed as $listedUrl) {
            if (rtrim((string) $listedUrl, '/') === $normalized) {
                return true;
            }
        }

        return false;
    }
}
