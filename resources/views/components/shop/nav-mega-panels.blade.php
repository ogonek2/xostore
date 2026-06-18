@props([
    'panels' => [],
])

@php
    $productLimit = max(1, (int) config('shop.mega_menu.product_limit', 4));

    $brandPanels = [];
    $linkPanels = [];
    $featuredProducts = [];
    $viewAll = null;

    foreach ($panels as $panel) {
        if (($panel['type'] ?? '') === 'brands' && ! empty($panel['brands'])) {
            $brandPanels[] = $panel;

            continue;
        }

        if (! empty($panel['links'])) {
            $linkPanels[] = $panel;
        }

        foreach ($panel['products'] ?? [] as $product) {
            $featuredProducts[] = $product;
        }

        if (! empty($panel['view_all_url']) && ($panel['type'] ?? '') !== 'brands') {
            $viewAll = [
                'url' => $panel['view_all_url'],
                'label' => $panel['view_all_label'] ?? __('shop.nav.view_all'),
            ];
        }
    }

    $featuredProducts = collect($featuredProducts)
        ->unique('url')
        ->take($productLimit)
        ->values()
        ->all();

    $hasBrands = $brandPanels !== [];
    $hasLinks = $linkPanels !== [];
    $hasProducts = $featuredProducts !== [];
    $brandsOnly = $hasBrands && ! $hasLinks && ! $hasProducts;
@endphp

<div @class([
    'mega-nav',
    'w-full' => $brandsOnly,
    'flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-10 xl:gap-12' => ($hasLinks || $hasProducts) && ! $brandsOnly,
    'grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' => $hasLinks && ! $hasBrands && ! $hasProducts && count($linkPanels) > 1,
    'max-w-xl' => $hasLinks && ! $hasBrands && ! $hasProducts && count($linkPanels) === 1,
])>
    @if ($hasBrands)
        <div @class([
            'mega-nav__brands min-w-0',
            'w-full' => $brandsOnly,
            'shrink-0 lg:flex-1' => ! $brandsOnly,
        ])>
            @foreach ($brandPanels as $panel)
                <div @class(['min-w-0', 'not-first:mt-8' => count($brandPanels) > 1])>
                    <div class="flex items-end justify-between gap-4">
                        @if (! empty($panel['title']))
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-text-muted">
                                {{ $panel['title'] }}
                            </p>
                        @endif

                        @if (! empty($panel['view_all_url']))
                            <a
                                href="{{ $panel['view_all_url'] }}"
                                class="shrink-0 text-xs font-medium text-primary-DEFAULT underline-offset-2 hover:underline"
                            >
                                {{ $panel['view_all_label'] ?? __('shop.nav.all_brands') }}
                            </a>
                        @endif
                    </div>

                    <div @class(['mt-4' => ! empty($panel['title']), 'mt-0' => empty($panel['title'])])>
                        <x-shop.nav-mega-brands-grid :brands="$panel['brands']" />
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($hasLinks)
        <div @class([
            'mega-nav__links min-w-0',
            'shrink-0 lg:w-[min(100%,17rem)] xl:w-[min(100%,19rem)]' => $hasProducts,
            'space-y-7' => $hasProducts || count($linkPanels) === 1,
        ])>
            @foreach ($linkPanels as $panel)
                <div class="min-w-0">
                    @if (! empty($panel['title']))
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-text-muted">
                            {{ $panel['title'] }}
                        </p>
                    @endif

                    @if (! empty($panel['links']))
                        <ul @class([
                            'space-y-1',
                            'mt-3' => ! empty($panel['title']),
                            'columns-2 gap-x-8' => ($panel['columns'] ?? 1) >= 2 && ! $hasProducts && ! $hasBrands,
                        ])>
                            @foreach ($panel['links'] as $link)
                                <li @class(['break-inside-avoid' => ($panel['columns'] ?? 1) >= 2])>
                                    <a
                                        href="{{ $link['url'] }}"
                                        class="block py-1.5 text-sm text-text-DEFAULT transition-colors hover:text-text-muted"
                                        @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                                    >
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($hasProducts)
        <div @class([
            'mega-nav__products min-w-0',
            'lg:max-w-[32rem] xl:max-w-[36rem]' => $hasLinks,
        ])>
            <div class="flex items-end justify-between gap-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-text-muted">
                    {{ __('shop.nav.featured_products') }}
                </p>
                @if ($viewAll)
                    <a
                        href="{{ $viewAll['url'] }}"
                        class="hidden shrink-0 text-xs font-medium text-primary-DEFAULT underline-offset-2 hover:underline sm:inline"
                    >
                        {{ $viewAll['label'] }}
                    </a>
                @endif
            </div>

            <div class="mt-3 flex flex-wrap content-start gap-2.5 sm:gap-3">
                @foreach ($featuredProducts as $product)
                    <x-shop.nav-mega-product-card :product="$product" />
                @endforeach
            </div>

            @if ($viewAll)
                <a
                    href="{{ $viewAll['url'] }}"
                    class="mt-4 inline-block text-sm font-medium text-primary-DEFAULT underline-offset-2 hover:underline sm:hidden"
                >
                    {{ $viewAll['label'] }}
                </a>
            @endif
        </div>
    @endif
</div>
