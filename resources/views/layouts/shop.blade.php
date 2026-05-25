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
<body class="flex min-h-screen flex-col bg-surface-DEFAULT font-sans text-text-DEFAULT antialiased">
    <div class="flex flex-1 flex-col">
        @yield('content')
    </div>

    <x-shop.footer :menu-roots="$menuRoots" :languages="$languages" />

    <div data-vue="cart-modal" id="cart-modal-root"></div>

    @stack('scripts')
</body>
</html>
