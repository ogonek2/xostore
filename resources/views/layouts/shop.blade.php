<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @isset($seo)
        <x-shop.seo-head :seo="$seo" />
    @else
        <title>{{ config('shop.name') }}</title>
    @endisset
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|cormorant-garamond:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body
    class="flex min-h-screen flex-col bg-surface-DEFAULT font-sans text-text-DEFAULT antialiased"
    data-analytics-endpoint="{{ route('api.analytics.store', ['locale' => app()->getLocale()]) }}"
>
    <div class="flex flex-1 flex-col">
        @yield('content')
    </div>

    <x-shop.footer :footer="$footer" :languages="$languages" />

    @php
        $locale = app()->getLocale();
        $cartLabels = [
            'title' => __('shop.cart.title'),
            'empty' => __('shop.cart.empty'),
            'subtotal' => __('shop.cart.subtotal'),
            'checkout' => __('shop.cart.checkout'),
            'continue' => __('shop.cart.continue'),
            'remove' => __('shop.cart.remove'),
            'loading' => __('shop.cart.loading'),
        ];
    @endphp
    <div
        id="cart-drawer-root"
        data-vue="cart-drawer"
        data-locale="{{ $locale }}"
        data-labels="{{ json_encode($cartLabels) }}"
        data-routes="{{ json_encode([
            'show' => route('api.cart.show', ['locale' => $locale]),
            'update' => route('api.cart.update', ['locale' => $locale, 'item' => '__ITEM__']),
            'destroy' => route('api.cart.destroy', ['locale' => $locale, 'item' => '__ITEM__']),
            'checkout' => route('checkout.show', ['locale' => $locale]),
        ]) }}"
    ></div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-cart-count]').forEach((el) => {
                window.addEventListener('cart:updated', (e) => {
                    const count = e.detail?.count ?? 0;
                    el.textContent = count;
                    el.classList.toggle('hidden', count < 1);
                });
            });
        });
    </script>

    @php
        $chatProvider = config('shop.chat.provider');
    @endphp

    @if ($chatProvider === 'telegram' && config('shop.chat.telegram_url'))
        <a
            href="{{ config('shop.chat.telegram_url') }}"
            target="_blank"
            rel="noopener noreferrer"
            style="position:fixed;right:18px;bottom:18px;z-index:9999;display:inline-flex;align-items:center;gap:8px;padding:10px 14px;background:#229ED9;color:#fff;border-radius:9999px;text-decoration:none;font:600 13px/1 Arial,sans-serif;box-shadow:0 10px 24px rgba(0,0,0,.2);"
            aria-label="Telegram chat"
        >
            <span>Telegram</span>
        </a>
    @elseif ($chatProvider === 'jivo' && config('shop.chat.jivo_widget_id'))
        <script src="//code.jivosite.com/widget/{{ config('shop.chat.jivo_widget_id') }}" async></script>
    @elseif ($chatProvider === 'crisp' && config('shop.chat.crisp_website_id'))
        <script>
            window.$crisp = [];
            window.CRISP_WEBSITE_ID = @json(config('shop.chat.crisp_website_id'));
            (function () {
                var d = document;
                var s = d.createElement('script');
                s.src = 'https://client.crisp.chat/l.js';
                s.async = 1;
                d.getElementsByTagName('head')[0].appendChild(s);
            })();
        </script>
    @elseif ($chatProvider === 'tawk' && config('shop.chat.tawk_property_id'))
        <script>
            var Tawk_API = Tawk_API || {};
            var Tawk_LoadStart = new Date();
            (function () {
                var s1 = document.createElement('script');
                var s0 = document.getElementsByTagName('script')[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/{{ config('shop.chat.tawk_property_id') }}/{{ config('shop.chat.tawk_widget_id', '1') }}';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
    @endif
</body>
</html>
