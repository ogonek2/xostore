@extends('layouts.shop')

@section('title', __('shop.checkout.title').' — '.config('shop.name'))

@section('content')
    <x-shop.header :menu-roots="$menuRoots" :languages="$languages" :cart-count="$cartCount" />

    <main class="flex-1">
        <div class="mx-auto max-w-[90rem] px-5 py-8 lg:px-8 lg:py-10">
            <x-shop.breadcrumbs :items="$breadcrumbs" />

            <h1 class="mt-6 text-2xl font-semibold tracking-tight lg:text-3xl">{{ __('shop.checkout.title') }}</h1>

            <form method="post" action="{{ route('checkout.store', ['locale' => app()->getLocale()]) }}" class="mt-10 grid gap-12 lg:grid-cols-[1fr_380px]">
                @csrf

                <div class="space-y-8">
                    <fieldset class="space-y-4">
                        <legend class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ __('shop.checkout.contact') }}</legend>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <input type="text" name="first_name" required placeholder="{{ __('shop.checkout.first_name') }}" value="{{ old('first_name') }}" class="border border-border-DEFAULT px-4 py-3 text-sm">
                            <input type="text" name="last_name" required placeholder="{{ __('shop.checkout.last_name') }}" value="{{ old('last_name') }}" class="border border-border-DEFAULT px-4 py-3 text-sm">
                        </div>
                        <input type="email" name="email" required placeholder="{{ __('shop.checkout.email') }}" value="{{ old('email') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                        <input type="tel" name="phone" placeholder="{{ __('shop.checkout.phone') }}" value="{{ old('phone') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                    </fieldset>

                    <fieldset class="space-y-4">
                        <legend class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ __('shop.checkout.shipping') }}</legend>
                        <input type="text" name="street" required placeholder="{{ __('shop.checkout.street') }}" value="{{ old('street') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <input type="text" name="city" required placeholder="{{ __('shop.checkout.city') }}" value="{{ old('city') }}" class="border border-border-DEFAULT px-4 py-3 text-sm">
                            <input type="text" name="postal_code" required placeholder="{{ __('shop.checkout.postal_code') }}" value="{{ old('postal_code') }}" class="border border-border-DEFAULT px-4 py-3 text-sm">
                        </div>
                        <input type="hidden" name="country" value="PL">
                        <textarea name="notes" rows="3" placeholder="{{ __('shop.checkout.notes') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">{{ old('notes') }}</textarea>
                    </fieldset>
                </div>

                <aside class="h-fit border border-border-DEFAULT p-6 lg:sticky lg:top-24">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.16em]">{{ __('shop.checkout.summary') }}</h2>
                    <ul class="mt-6 space-y-4">
                        @foreach ($cart['items'] as $item)
                            <li class="flex justify-between gap-4 text-sm">
                                <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                                <span class="shrink-0 font-medium">{{ $item['line_total_formatted'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-6 space-y-2 border-t border-border-DEFAULT pt-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-text-muted">{{ __('shop.cart.subtotal') }}</span>
                            <span>{{ $cart['subtotal_formatted'] }}</span>
                        </div>
                        @php
                            $shipping = config('shop.checkout.shipping_cost', 0);
                            if ($cart['subtotal'] >= config('shop.checkout.free_shipping_from', 0) && config('shop.checkout.free_shipping_from', 0) > 0) {
                                $shipping = 0;
                            }
                        @endphp
                        <div class="flex justify-between">
                            <span class="text-text-muted">{{ __('shop.checkout.shipping_cost') }}</span>
                            <span>{{ $shipping > 0 ? number_format($shipping, 0, ',', ' ').' '.config('shop.currency_symbol') : __('shop.checkout.free') }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold">
                            <span>{{ __('shop.checkout.total') }}</span>
                            <span>{{ number_format($cart['subtotal'] + $shipping, 0, ',', ' ') }} {{ config('shop.currency_symbol') }}</span>
                        </div>
                    </div>
                    <button type="submit" class="mt-6 w-full bg-primary-DEFAULT py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                        {{ __('shop.checkout.place_order') }}
                    </button>
                </aside>
            </form>
        </div>
    </main>
@endsection
