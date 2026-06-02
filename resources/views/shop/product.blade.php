@php
    $productLabels = [
        'color' => __('shop.product.color'),
        'size' => __('shop.product.select_variant'),
        'quantity' => __('shop.product.quantity'),
        'add_to_cart' => __('shop.product.add_to_cart'),
        'add_another' => __('shop.product.add_another'),
        'adding' => __('shop.product.adding'),
        'in_cart' => __('shop.cart.in_cart'),
        'in_cart_notice' => __('shop.product.in_cart_notice'),
        'view_cart' => __('shop.product.view_cart'),
        'in_cart_variant' => __('shop.product.in_cart_variant'),
        'in_cart_total' => __('shop.product.in_cart_total'),
        'consultation' => __('shop.consultation.cta'),
        'new_badge' => __('shop.new_arrivals.badge'),
        'cart_label' => __('shop.cart.label'),
        'colors' => __('shop.product.colors'),
        'prev' => __('shop.product.gallery_prev'),
        'next' => __('shop.product.gallery_next'),
        'select_size' => __('shop.product.select_size'),
        'error' => __('shop.cart.error'),
        'characteristics' => __('shop.product.characteristics'),
        'similar_products' => __('shop.product.similar_products'),
        'tab_details' => __('shop.product.tab_details'),
        'tab_fit' => __('shop.product.tab_fit'),
        'tab_fabric' => __('shop.product.tab_fabric'),
        'no_details' => __('shop.product.no_details'),
        'close' => __('shop.product.gallery_close'),
        'prev' => __('shop.product.gallery_prev'),
        'next' => __('shop.product.gallery_next'),
        'zoom_in' => __('shop.product.gallery_zoom_in'),
        'zoom_out' => __('shop.product.gallery_zoom_out'),
        'thumbnail' => __('shop.product.gallery_thumbnail'),
    ];
@endphp

@extends('layouts.shop')

@section('title', $product['display_name'].' — '.config('shop.name'))

@section('content')
    <x-shop.header
        :navigation="$navigation"
        :languages="$languages"
        :cart-count="$cartCount"
    />

    <main class="flex-1">
        <div class="mx-auto max-w-[90rem] px-5 py-8 lg:px-8 lg:py-10">
            <x-shop.breadcrumbs :items="$breadcrumbs" />

            <div
                class="mt-8"
                data-vue="product-page"
                data-product="{{ json_encode($product) }}"
                data-labels="{{ json_encode($productLabels) }}"
                data-routes="{{ json_encode([
                    'cartStore' => route('api.cart.store', ['locale' => app()->getLocale()]),
                ]) }}"
            >
                <noscript>
                    <p class="text-sm text-text-muted">{{ __('shop.product.js_required') }}</p>
                </noscript>
            </div>
        </div>
    </main>
@endsection
