@extends('layouts.shop')

@section('content')
    <x-shop.header :navigation="$navigation" :languages="$languages" :cart-count="$cartCount" />

    <main class="flex-1">
        <div class="mx-auto max-w-2xl px-5 py-16 lg:px-8">
            <div class="text-center">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-muted">{{ __('shop.checkout.thankyou_title') }}</p>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight">{{ __('shop.checkout.thankyou_heading') }}</h1>
                <p class="mt-4 text-sm text-text-muted">{{ __('shop.checkout.thankyou_text') }}</p>
                <p class="mt-8 font-mono text-2xl font-semibold tracking-widest">{{ $order->number }}</p>
                <p class="mt-2 text-sm text-text-muted">
                    {{ __('shop.checkout.order_total') }}:
                    {{ number_format($order->total, 0, ',', ' ') }} {{ config('shop.currency_symbol') }}
                </p>
                @if ($order->paymentMethod)
                    <p class="mt-2 text-sm text-text-muted">
                        {{ __('shop.checkout.payment_method') }}: {{ $order->paymentMethod->label(app()->getLocale()) }}
                    </p>
                @endif
            </div>

            @if ($bank && ($bank['account'] || $bank['recipient']))
                <div class="mt-10 border border-border-DEFAULT bg-surface-muted/40 p-6 text-left text-sm">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.16em] text-text-muted">{{ __('shop.checkout.bank_title') }}</h2>
                    <dl class="mt-4 space-y-2">
                        @if ($bank['recipient'])
                            <div><dt class="text-text-muted">{{ __('shop.checkout.bank_recipient') }}</dt><dd class="font-medium">{{ $bank['recipient'] }}</dd></div>
                        @endif
                        @if ($bank['bank_name'])
                            <div><dt class="text-text-muted">{{ __('shop.checkout.bank_name') }}</dt><dd>{{ $bank['bank_name'] }}</dd></div>
                        @endif
                        @if ($bank['account'])
                            <div><dt class="text-text-muted">{{ __('shop.checkout.bank_account') }}</dt><dd class="font-mono">{{ $bank['account'] }}</dd></div>
                        @endif
                        <div><dt class="text-text-muted">{{ __('shop.checkout.bank_amount') }}</dt><dd class="font-semibold">{{ number_format($order->total, 2, ',', ' ') }} {{ config('shop.currency_symbol') }}</dd></div>
                        <div><dt class="text-text-muted">{{ __('shop.checkout.bank_reference') }}</dt><dd class="font-mono font-medium">{{ $bank['payment_note'] }}</dd></div>
                    </dl>
                </div>
            @endif

            @if ($order->paymentMethod?->instructionsText(app()->getLocale()))
                <p class="mt-6 text-center text-sm text-text-muted">{{ $order->paymentMethod->instructionsText(app()->getLocale()) }}</p>
            @endif

            <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                <a href="{{ route('order.track', ['locale' => app()->getLocale()]) }}" class="inline-flex border border-border-DEFAULT px-8 py-3.5 text-sm font-medium text-text-DEFAULT hover:bg-surface-muted">
                    {{ __('shop.order_track.link') }}
                </a>
                <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="inline-flex bg-primary-DEFAULT px-8 py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                    {{ __('shop.listing.continue_shopping') }}
                </a>
            </div>
        </div>
    </main>
@endsection
