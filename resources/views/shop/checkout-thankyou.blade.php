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

            @php($payment = $order->latestPayment)
            @if ($payment)
                <div class="mt-8 border border-border-DEFAULT bg-surface-muted/40 p-6 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-muted">{{ __('shop.checkout.payment_status') }}</p>
                    <p class="mt-2 font-medium">{{ __('shop.checkout.payment_statuses.'.$payment->status->value) }}</p>

                    @if (session('payment_retry_failed'))
                        <p class="mt-3 text-sm text-sale-DEFAULT" role="alert">{{ __('shop.checkout.payment_retry_failed') }}</p>
                    @endif

                    @if ($payment->status !== \App\Enums\PaymentStatus::Paid)
                        <form method="post" action="{{ route('payments.retry', ['locale' => app()->getLocale(), 'order' => $order->access_token, 'payment' => $payment->public_token]) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="inline-flex bg-primary-DEFAULT px-8 py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                                {{ __('shop.checkout.payment_retry') }}
                            </button>
                        </form>
                    @endif
                </div>
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

{{-- purchase: only after successful order (COD/bank) or Paid gateway payment. Before GTM. --}}
@push('datalayer')
    <script>
        (function () {
            window.dataLayer = window.dataLayer || [];

            var ecommerce = @json($purchaseEcommerce ?? null);
            var shouldTrack = @json((bool) ($shouldTrackPurchase ?? false));
            var pollUrl = @json($paymentStatusUrl ?? null);
            var storageKey = ecommerce && ecommerce.transaction_id
                ? ('ga4_purchase_' + ecommerce.transaction_id)
                : null;

            function alreadyTracked() {
                if (!storageKey) {
                    return true;
                }
                try {
                    return sessionStorage.getItem(storageKey) === '1';
                } catch (e) {
                    return false;
                }
            }

            function markTracked() {
                if (!storageKey) {
                    return;
                }
                try {
                    sessionStorage.setItem(storageKey, '1');
                } catch (e) {}
            }

            function pushPurchase() {
                if (!ecommerce || !ecommerce.transaction_id || alreadyTracked()) {
                    return;
                }

                dataLayer.push({ ecommerce: null });
                dataLayer.push({
                    event: 'purchase',
                    ecommerce: {
                        transaction_id: ecommerce.transaction_id,
                        value: Number(ecommerce.value),
                        currency: ecommerce.currency,
                        shipping: ecommerce.shipping != null ? Number(ecommerce.shipping) : undefined,
                        affiliation: ecommerce.affiliation || undefined,
                        items: ecommerce.items || []
                    }
                });
                markTracked();
            }

            if (shouldTrack) {
                pushPurchase();
                return;
            }

            if (!pollUrl || !ecommerce) {
                return;
            }

            var attempts = 0;
            var maxAttempts = 20;
            var timer = setInterval(function () {
                attempts += 1;
                if (attempts > maxAttempts) {
                    clearInterval(timer);
                    return;
                }

                fetch(pollUrl, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.ok ? response.json() : null; })
                    .then(function (data) {
                        if (data && data.paid) {
                            clearInterval(timer);
                            pushPurchase();
                        }
                    })
                    .catch(function () {});
            }, 2000);
        })();
    </script>
@endpush
