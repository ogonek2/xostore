<?php

namespace App\Support\Shop;

use App\Models\NavMenu;
use Illuminate\Support\Facades\Schema;

class ShopNavigation
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function tree(string $code): array
    {
        if (! Schema::hasTable('nav_menus') || ! Schema::hasTable('nav_items')) {
            return [];
        }

        $menu = NavMenu::query()
            ->active()
            ->where('code', $code)
            ->with([
                'items' => fn ($q) => $q
                    ->where('is_active', true)
                    ->with([
                        'children' => fn ($c) => $c
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                        'panels' => fn ($p) => $p
                            ->where('is_active', true)
                            ->with([
                                'links' => fn ($l) => $l->orderBy('sort_order'),
                                'category.translates',
                                'catalog.translates',
                                'products' => fn ($pr) => $pr->orderByPivot('sort_order'),
                            ])
                            ->orderBy('sort_order'),
                    ])
                    ->orderBy('sort_order'),
            ])
            ->first();

        if (! $menu || $menu->items->isEmpty()) {
            return [];
        }

        $locale = app()->getLocale();

        return $menu->items
            ->map(fn ($item) => static::mapItem($item, $locale))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function mapItem($item, string $locale): ?array
    {
        $label = $item->label($locale);

        if (! $label) {
            return null;
        }

        $url = static::resolveUrl($item->url, $locale);

        $panels = $item->panels
            ->map(fn ($panel) => NavPanelPresenter::present($panel, $locale))
            ->filter()
            ->values()
            ->all();

        if ($panels !== []) {
            return [
                'type' => 'mega',
                'label' => $label,
                'url' => $url,
                'open_in_new_tab' => (bool) $item->open_in_new_tab,
                'panels' => $panels,
            ];
        }

        $children = $item->children
            ->map(fn ($child) => static::mapItem($child, $locale))
            ->filter()
            ->values()
            ->all();

        if ($url === null && $children === []) {
            return null;
        }

        return [
            'type' => 'simple',
            'label' => $label,
            'url' => $url,
            'open_in_new_tab' => (bool) $item->open_in_new_tab,
            'children' => $children,
        ];
    }

    public static function resolveUrl(?string $url, string $locale): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        return '/'.$locale.'/'.ltrim($url, '/');
    }
}
