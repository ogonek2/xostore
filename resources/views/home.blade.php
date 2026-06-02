@extends('layouts.shop')

@section('title', config('shop.name'))

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
        <x-shop.categories-showcase :cards="$categoryCards" />
        <div id="trending">
            <x-shop.trending-section :products="$trendingProducts" />
        </div>
        <x-shop.promotions-section
            :featured="$promotions['featured']"
            :compact="$promotions['compact']"
            :view-all-url="$promotions['view_all_url']"
            :view-more-url="$promotions['view_all_url']"
        />
        <x-shop.new-arrivals-section
            :products="$newArrivals['products']"
            :view-all-url="$newArrivals['view_all_url']"
        />
    </main>
@endsection
