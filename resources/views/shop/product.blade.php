@php
    $productLabels = [
        'color' => __('shop.product.color'),
        'size' => __('shop.product.select_variant'),
        'quantity' => __('shop.product.quantity'),
        'add_to_cart' => __('shop.product.add_to_cart'),
        'adding' => __('shop.product.adding'),
        'consultation' => __('shop.consultation.cta'),
        'new_badge' => __('shop.new_arrivals.badge'),
        'select_size' => __('shop.product.select_size'),
        'error' => __('shop.cart.error'),
        'tab_details' => __('shop.product.tab_details'),
        'tab_fit' => __('shop.product.tab_fit'),
        'tab_fabric' => __('shop.product.tab_fabric'),
        'no_details' => __('shop.product.no_details'),
    ];
@endphp

@extends('layouts.shop')

@section('title', $product['display_name'].' — '.config('shop.name'))

@section('content')
    <x-shop.header
        :menu-roots="$menuRoots"
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
