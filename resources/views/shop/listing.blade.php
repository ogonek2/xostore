@php
    $locale = app()->getLocale();

    $listingCategories = $menuRoots
        ->values()
        ->map(function ($root) use ($locale) {
            $rootSlug = $root->translate('slug', $locale) ?? $root->code;

            return [
                'label' => $root->translate('name', $locale) ?? $root->code,
                'url' => route('category.show', ['locale' => $locale, 'category' => $rootSlug]),
                'children' => $root->children
                    ->map(function ($child) use ($locale) {
                        $slug = $child->translate('slug', $locale) ?? $child->code;

                        return [
                            'label' => $child->translate('name', $locale) ?? $child->code,
                            'url' => route('category.show', ['locale' => $locale, 'category' => $slug]),
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        })
        ->all();

    $listingLabels = [
        'filters' => __('shop.listing.filters'),
        'categories' => __('shop.categories.title'),
        'open_filters' => __('shop.listing.filters'),
        'close' => __('shop.product.gallery_close'),
        'sort' => __('shop.listing.sort'),
        'sort_newest' => __('shop.listing.sort_newest'),
        'sort_featured' => __('shop.listing.sort_featured'),
        'sort_price_asc' => __('shop.listing.sort_price_asc'),
        'sort_price_desc' => __('shop.listing.sort_price_desc'),
        'brand' => __('shop.listing.brand'),
        'size' => __('shop.product.select_variant'),
        'size_hint' => __('shop.listing.size_hint'),
        'color' => __('shop.listing.color'),
        'price' => __('shop.listing.price'),
        'from' => __('shop.listing.from'),
        'to' => __('shop.listing.to'),
        'apply_price' => __('shop.listing.apply_price'),
        'clear_filters' => __('shop.listing.clear_filters'),
        'only_new' => __('shop.listing.only_new'),
        'only_sale' => __('shop.listing.only_sale'),
        'search' => __('shop.search'),
        'search_placeholder' => __('shop.listing.search_placeholder'),
        'empty' => __('shop.listing.empty'),
        'loading' => __('shop.listing.loading'),
        'cart' => __('shop.cart.label'),
        'in_cart' => __('shop.cart.in_cart'),
        'in_cart_label' => __('shop.cart.in_cart_label', ['name' => ':name']),
        'colors' => __('shop.product.colors'),
        'new_badge' => __('shop.new_arrivals.badge'),
    ];
@endphp

@extends('layouts.shop')

@section('content')
    <x-shop.header
        :navigation="$navigation"
        :languages="$languages"
        :cart-count="$cartCount"
    />

    <main class="flex-1">
        <div class="mx-auto max-w-[90rem] px-5 py-8 lg:px-8 lg:py-10">
            <x-shop.breadcrumbs :items="$breadcrumbs" />

            <div class="mt-6">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-primary-DEFAULT lg:text-3xl">
                        {{ $pageTitle }}
                    </h1>
                    <p id="listing-products-count" class="mt-2 text-sm text-text-muted">
                        {{ trans_choice('shop.listing.products_count', $total, ['count' => $total]) }}
                    </p>
                </div>
            </div>

            @if ($subcategories->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($subcategories as $sub)
                        <a
                            href="{{ $sub['url'] }}"
                            class="border border-border-DEFAULT px-4 py-2 text-sm transition-colors hover:border-primary-DEFAULT hover:bg-surface-muted"
                        >
                            {{ $sub['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div
                class="mt-10"
                data-vue="catalog-page"
                data-endpoint="{{ $apiEndpoint }}"
                data-base-query="{{ json_encode($apiQuery) }}"
                data-initial-filters="{{ json_encode($filters) }}"
                data-initial-items="{{ json_encode($products->values()) }}"
                data-initial-total="{{ $total }}"
                data-facets="{{ json_encode($facets) }}"
                data-categories="{{ json_encode($listingCategories) }}"
                data-labels="{{ json_encode($listingLabels) }}"
                data-locale="{{ app()->getLocale() }}"
                data-per-page="{{ config('shop.listing.per_page', 24) }}"
            >
                <noscript>
                    <div class="grid gap-10 lg:grid-cols-[240px_minmax(0,1fr)]">
                        <aside>
                            <x-shop.catalog-filters
                                :facets="$facets"
                                :filters="$filters"
                                :form-action="url()->current()"
                            />
                        </aside>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                            @foreach ($products as $product)
                                <x-shop.product-card
                                    :product-id="$product['product_id']"
                                    :url="$product['url']"
                                    :name="$product['name']"
                                    :category="$product['category']"
                                    :price-formatted="$product['price_formatted']"
                                    :image="$product['image']"
                                    :colors="$product['colors']"
                                    :show-new-badge="$product['is_new'] ?? false"
                                />
                            @endforeach
                        </div>
                    </div>
                </noscript>
            </div>
        </div>
    </main>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const mount = document.querySelector('[data-vue="catalog-page"]');
                const countEl = document.getElementById('listing-products-count');
                if (!mount) return;

                const syncCount = (text) => {
                    if (countEl) countEl.textContent = text;
                };

                mount.addEventListener('catalog:count', (e) => syncCount(e.detail));
            });
        </script>
    @endpush
@endsection
