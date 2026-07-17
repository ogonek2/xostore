@extends('layouts.shop')

@section('content')
    <x-shop.header :navigation="$navigation" :languages="$languages" :cart-count="$cartCount" />

    <main class="flex-1">
        <div class="mx-auto max-w-xl px-5 py-12 lg:px-8 lg:py-16">
            <x-shop.breadcrumbs :items="$breadcrumbs" />

            <h1 class="mt-6 text-2xl font-semibold tracking-tight">{{ __('shop.order_track.title') }}</h1>
            <p class="mt-2 text-sm text-text-muted">{{ __('shop.order_track.hint') }}</p>

            <form method="post" action="{{ route('order.track.lookup', ['locale' => app()->getLocale()]) }}" class="mt-8 space-y-4">
                @csrf
                <div>
                    <label for="order-number" class="sr-only">{{ __('shop.order_track.number') }}</label>
                    <input
                        id="order-number"
                        type="text"
                        name="number"
                        required
                        maxlength="8"
                        pattern="[0-9A-Za-z]{8}"
                        placeholder="{{ __('shop.order_track.number_placeholder') }}"
                        value="{{ old('number', request('number')) }}"
                        class="w-full border border-border-DEFAULT px-4 py-3 font-mono text-sm uppercase tracking-widest"
                    >
                </div>
                <div>
                    <label for="order-email" class="sr-only">{{ __('shop.checkout.email') }}</label>
                    <input
                        id="order-email"
                        type="email"
                        name="email"
                        required
                        placeholder="{{ __('shop.checkout.email') }}"
                        value="{{ old('email') }}"
                        class="w-full border border-border-DEFAULT px-4 py-3 text-sm"
                    >
                </div>
                <button type="submit" class="w-full bg-primary-DEFAULT py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                    {{ __('shop.order_track.submit') }}
                </button>
            </form>

            @if (($notFound ?? false) === true)
                <p class="mt-6 text-sm text-sale-DEFAULT" role="alert">{{ __('shop.order_track.not_found') }}</p>
            @endif

            @if ($order)
                <div class="mt-10 border border-border-DEFAULT p-6">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <span class="font-mono text-xl font-semibold tracking-widest">{{ $order->number }}</span>
                        <span class="rounded-full bg-surface-muted px-3 py-1 text-xs font-medium">{{ $order->statusLabel() }}</span>
                    </div>
                    <p class="mt-4 text-sm text-text-muted">{{ __('shop.order_track.placed_at') }}: {{ $order->placed_at?->format('d.m.Y H:i') }}</p>

                    <ul class="mt-6 space-y-3 border-t border-border-DEFAULT pt-6 text-sm">
                        @foreach ($order->items as $item)
                            <li class="flex justify-between gap-4">
                                <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                                <span class="shrink-0">{{ number_format($item->total_price, 0, ',', ' ') }} {{ config('shop.currency_symbol') }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 space-y-1 border-t border-border-DEFAULT pt-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-text-muted">{{ __('shop.cart.subtotal') }}</span>
                            <span>{{ number_format($order->subtotal, 0, ',', ' ') }} {{ config('shop.currency_symbol') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-text-muted">{{ __('shop.checkout.shipping_cost') }}</span>
                            <span>{{ $order->shipping > 0 ? number_format($order->shipping, 0, ',', ' ').' '.config('shop.currency_symbol') : __('shop.checkout.free') }}</span>
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span>{{ __('shop.checkout.total') }}</span>
                            <span>{{ number_format($order->total, 0, ',', ' ') }} {{ config('shop.currency_symbol') }}</span>
                        </div>
                    </div>

                    @if ($order->paymentMethod)
                        <p class="mt-4 text-sm text-text-muted">
                            {{ __('shop.checkout.payment_method') }}: {{ $order->paymentMethod->label(app()->getLocale()) }}
                        </p>
                    @endif
                    @php($trackingPayment = $order->latestPayment)
                    @if ($trackingPayment)
                        <p class="mt-2 text-sm text-text-muted">
                            {{ __('shop.checkout.payment_status') }}:
                            {{ __('shop.checkout.payment_statuses.'.$trackingPayment->status->value) }}
                        </p>
                        @if ($trackingPayment->status !== \App\Enums\PaymentStatus::Paid)
                            <form method="post" action="{{ route('payments.retry', ['locale' => app()->getLocale(), 'order' => $order->access_token, 'payment' => $trackingPayment->public_token]) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="inline-flex bg-primary-DEFAULT px-6 py-3 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                                    {{ __('shop.checkout.payment_retry') }}
                                </button>
                            </form>
                        @endif
                    @endif

                    <dl class="mt-6 space-y-2 border-t border-border-DEFAULT pt-6 text-sm">
                        <div><dt class="text-text-muted">{{ __('shop.checkout.customer_name') }}</dt><dd>{{ $order->displayName() }}</dd></div>
                        <div><dt class="text-text-muted">{{ __('shop.checkout.city') }}</dt><dd>{{ $order->city }}</dd></div>
                        <div><dt class="text-text-muted">{{ __('shop.checkout.delivery_address') }}</dt><dd>{{ $order->displayAddress() }}</dd></div>
                    </dl>
                </div>
            @endif
        </div>
    </main>
@endsection
