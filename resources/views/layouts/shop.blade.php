<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('shop.name'))</title>
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

    <x-shop.footer :menu-roots="$menuRoots" :languages="$languages" />

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
</body>
</html>
