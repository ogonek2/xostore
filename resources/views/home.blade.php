@extends('layouts.shop')

@section('content')
    <x-shop.header
        :navigation="$navigation"
        :languages="$languages"
        :cart-count="$cartCount"
    />

    <main class="flex-1 overflow-x-clip">
        <x-shop.hero-builder :sections="$heroBannerSections" />
        @if ($bannersEnabled ?? true)
            <x-shop.banners-section :items="$banners" />
        @endif
        @if ($homepage->show_category_showcase ?? true)
            <x-shop.categories-showcase :cards="$categoryCards" />
        @endif
        @if ($homepage->show_trending_section ?? true)
            <div id="trending">
                <x-shop.trending-section :products="$trendingProducts" />
            </div>
        @endif
        @if ($homepage->show_promotions_section ?? true)
            <x-shop.promotions-section
                :featured="$promotions['featured']"
                :compact="$promotions['compact']"
                :view-all-url="$promotions['view_all_url']"
                :view-more-url="$promotions['view_all_url']"
            />
        @endif
        @if ($homepage->show_new_arrivals_section ?? true)
            <x-shop.new-arrivals-section
                :products="$newArrivals['products']"
                :view-all-url="$newArrivals['view_all_url']"
            />
        @endif
    </main>
@endsection
