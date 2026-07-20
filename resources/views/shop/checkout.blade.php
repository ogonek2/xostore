@extends('layouts.shop')

@section('content')
    <x-shop.header :navigation="$navigation" :languages="$languages" :cart-count="$cartCount" />

    <main class="flex-1">
        <div class="mx-auto max-w-[90rem] px-5 py-8 lg:px-8 lg:py-10">
            <x-shop.breadcrumbs :items="$breadcrumbs" />

            <h1 class="mt-6 text-2xl font-semibold tracking-tight lg:text-3xl">{{ __('shop.checkout.title') }}</h1>

            @if ($paymentMethods->isEmpty())
                <p class="mt-8 text-sm text-sale-DEFAULT">{{ __('shop.checkout.no_payment_methods') }}</p>
            @else
                <form
                    method="post"
                    action="{{ route('checkout.store', ['locale' => app()->getLocale()]) }}"
                    class="mt-10 grid gap-12 lg:grid-cols-[1fr_380px]"
                    data-checkout-form
                    data-quote-url="{{ $quoteUrl }}"
                    data-subtotal="{{ $cart['subtotal'] }}"
                    data-currency="{{ config('shop.currency_symbol') }}"
                >
                    @csrf

                    <div class="space-y-8">
                        <fieldset class="space-y-4">
                            <legend class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ __('shop.checkout.contact') }}</legend>
                            <input type="text" name="customer_name" required placeholder="{{ __('shop.checkout.customer_name') }}" value="{{ old('customer_name') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                            <input type="email" name="email" required placeholder="{{ __('shop.checkout.email') }}" value="{{ old('email') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                            <input type="tel" name="phone" required placeholder="{{ __('shop.checkout.phone') }}" value="{{ old('phone') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                        </fieldset>

                        <fieldset class="space-y-4">
                            <legend class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ __('shop.checkout.shipping') }}</legend>

                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ([
                                    'courier' => __('shop.checkout.delivery_methods.courier'),
                                    'paczkomat' => __('shop.checkout.delivery_methods.paczkomat'),
                                ] as $method => $label)
                                    <label class="flex cursor-pointer gap-3 border border-border-DEFAULT p-4 transition has-[:checked]:border-primary-DEFAULT has-[:checked]:bg-surface-muted/50">
                                        <input
                                            type="radio"
                                            name="delivery_method"
                                            value="{{ $method }}"
                                            class="mt-1"
                                            @checked(old('delivery_method', 'courier') === $method)
                                            required
                                        >
                                        <span class="text-sm font-medium text-text-DEFAULT">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <input type="text" name="city" required placeholder="{{ __('shop.checkout.city') }}" value="{{ old('city') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm sm:col-span-1" autocomplete="address-level2">
                                <input type="text" name="postal_code" required placeholder="{{ __('shop.checkout.postal_code') }}" value="{{ old('postal_code') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm" autocomplete="postal-code" inputmode="numeric" pattern="[0-9]{2}-?[0-9]{3}" maxlength="6">
                            </div>
                            <input type="text" name="street" required placeholder="{{ __('shop.checkout.street') }}" value="{{ old('street') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm" autocomplete="street-address">
                            <input type="hidden" name="country" value="PL">
                            <textarea name="notes" rows="3" placeholder="{{ __('shop.checkout.notes') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">{{ old('notes') }}</textarea>
                        </fieldset>

                        <fieldset class="space-y-4">
                            <legend class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ __('shop.checkout.payment') }}</legend>
                            <div class="space-y-3">
                                @foreach ($paymentMethods as $index => $method)
                                    <label class="flex cursor-pointer gap-3 border border-border-DEFAULT p-4 transition has-[:checked]:border-primary-DEFAULT has-[:checked]:bg-surface-muted/50">
                                        <input
                                            type="radio"
                                            name="payment_method_id"
                                            value="{{ $method['id'] }}"
                                            class="mt-1"
                                            data-payment-method
                                            @checked(old('payment_method_id', $paymentMethods->first()['id'] ?? null) == $method['id'] || ($index === 0 && ! old('payment_method_id')))
                                            required
                                        >
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-medium text-text-DEFAULT">{{ $method['label'] }}</span>
                                            @if ($method['instructions'])
                                                <span class="mt-1 block text-xs text-text-muted">{{ $method['instructions'] }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
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
                            <div class="flex justify-between">
                                <span class="text-text-muted">{{ __('shop.checkout.shipping_cost') }}</span>
                                <span data-checkout-shipping>{{ __('shop.checkout.loading') }}</span>
                            </div>
                            <div class="flex justify-between text-base font-semibold">
                                <span>{{ __('shop.checkout.total') }}</span>
                                <span data-checkout-total>—</span>
                            </div>
                        </div>
                        <button type="submit" class="mt-6 w-full bg-primary-DEFAULT py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                            {{ __('shop.checkout.place_order') }}
                        </button>
                        <p class="mt-4 text-center">
                            <a href="{{ route('order.track', ['locale' => app()->getLocale()]) }}" class="text-xs text-text-muted underline hover:text-text-DEFAULT">
                                {{ __('shop.order_track.link') }}
                            </a>
                        </p>
                    </aside>
                </form>
            @endif
        </div>
    </main>
@endsection

@push('scripts')
    @vite(['resources/js/shop/checkout.js'])
@endpush
