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

    /** @var list<string> */
    private const SHOP_LABELS = ['sklep', 'shop', 'store'];

    /** @var list<string> */
    private const CONTACT_LABELS = ['kontakt', 'contact', 'konsultacja', 'consultation'];

    /**
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
     * @param  list<array<string, mixed>>  $catalogMegaItems
     * @return array{
     *     quick_actions: list<array<string, mixed>>,
     *     collections: list<array<string, mixed>>,
     *     pages: list<array<string, mixed>>,
     *     dropdowns: list<array<string, mixed>>
     * }
     */
    public static function menu(int $cartCount, Collection $simpleNavItems, array $catalogMegaItems): array
    {
        $locale = app()->getLocale();
        $catalogUrls = static::collectUrls($catalogMegaItems);
        $collectionUrls = static::collectionUrls($locale);

        $quickActions = [
            [
                'label' => __('shop.search'),
                'action' => 'search',
            ],
            [
                'label' => __('shop.cart.label'),
                'action' => 'cart',
                'badge' => $cartCount,
            ],
        ];

        $collections = [
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
        ];

        $pages = [];
        $dropdowns = [];
        $seenLabels = [];
        $seenUrls = [];

        foreach ($simpleNavItems as $item) {
            if (! empty($item['children'])) {
                $dropdowns[] = $item;

                continue;
            }

            $url = $item['url'] ?? null;

            if (! $url || static::urlIsListed($url, $catalogUrls) || static::urlIsListed($url, $collectionUrls)) {
                continue;
            }

            if (static::registerLink($item, $seenLabels, $seenUrls)) {
                $pages[] = [
                    'label' => $item['label'],
                    'url' => $url,
                    'open_in_new_tab' => (bool) ($item['open_in_new_tab'] ?? false),
                ];
            }
        }

        if (! static::hasLabelKind($pages, self::SHOP_LABELS)) {
            $shopLink = [
                'label' => __('shop.nav.shop'),
                'url' => route('products.index', ['locale' => $locale]),
            ];

            if (static::registerLink($shopLink, $seenLabels, $seenUrls)) {
                $pages[] = $shopLink;
            }
        }

        if (! static::hasLabelKind($pages, self::CONTACT_LABELS)) {
            $contactLink = [
                'label' => __('shop.nav.contact'),
                'url' => route('consultation.show', ['locale' => $locale]),
            ];

            if (static::registerLink($contactLink, $seenLabels, $seenUrls)) {
                $pages[] = $contactLink;
            }
        }

        return [
            'quick_actions' => $quickActions,
            'collections' => $collections,
            'pages' => $pages,
            'dropdowns' => $dropdowns,
        ];
    }

    /**
     * @deprecated Use menu() instead.
     *
     * @param  list<array<string, mixed>>  $catalogMegaItems
     * @return list<array<string, mixed>>
     */
    public static function utilityLinks(int $cartCount, Collection $simpleNavItems, array $catalogMegaItems): array
    {
        $menu = static::menu($cartCount, $simpleNavItems, $catalogMegaItems);

        return collect($menu['quick_actions'])
            ->merge($menu['collections'])
            ->merge($menu['pages'])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    protected static function collectionUrls(string $locale): array
    {
        return [
            rtrim(route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'promotions' : 'promocje']), '/'),
            rtrim(route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'new-in' : 'nowynki']), '/'),
            rtrim(route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'trends' : 'trendy']), '/'),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $links
     * @param  list<string>  $kinds
     */
    protected static function hasLabelKind(array $links, array $kinds): bool
    {
        foreach ($links as $link) {
            $label = static::normalizeLabel((string) ($link['label'] ?? ''));

            if (in_array($label, $kinds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, bool>  $seenLabels
     * @param  list<string>  $seenUrls
     */
    protected static function registerLink(array $link, array &$seenLabels, array &$seenUrls): bool
    {
        $label = static::normalizeLabel((string) ($link['label'] ?? ''));
        $url = rtrim((string) ($link['url'] ?? ''), '/');

        if ($label === '' || $url === '') {
            return false;
        }

        if (isset($seenLabels[$label]) || in_array($url, $seenUrls, true)) {
            return false;
        }

        $seenLabels[$label] = true;
        $seenUrls[] = $url;

        return true;
    }

    protected static function normalizeLabel(string $label): string
    {
        return mb_strtolower(trim($label));
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
        if (! empty($panel['links']) || ! empty($panel['brands'])) {
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
