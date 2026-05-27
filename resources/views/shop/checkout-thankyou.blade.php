@extends('layouts.shop')

@section('title', __('shop.checkout.thankyou_title').' — '.config('shop.name'))

@section('content')
    <x-shop.header :menu-roots="$menuRoots" :languages="$languages" :cart-count="$cartCount" />

    <main class="flex-1">
        <div class="mx-auto max-w-2xl px-5 py-16 text-center lg:px-8">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-muted">{{ __('shop.checkout.thankyou_title') }}</p>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight">{{ __('shop.checkout.thankyou_heading') }}</h1>
            <p class="mt-4 text-sm text-text-muted">{{ __('shop.checkout.thankyou_text') }}</p>
            <p class="mt-8 text-lg font-semibold">{{ $order->number }}</p>
            <p class="mt-2 text-sm text-text-muted">{{ __('shop.checkout.order_total') }}: {{ number_format($order->total, 0, ',', ' ') }} {{ config('shop.currency_symbol') }}</p>
            <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="mt-10 inline-flex bg-primary-DEFAULT px-8 py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                {{ __('shop.listing.continue_shopping') }}
            </a>
        </div>
    </main>
@endsection
