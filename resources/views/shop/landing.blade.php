@extends('layouts.shop')

@section('content')
    @if ($landing['show_header'] ?? true)
        <x-shop.header
            :navigation="$navigation"
            :languages="$languages"
            :cart-count="$cartCount"
        />
    @endif

    <main class="landing-page flex-1">
        <x-shop.landing.builder :blocks="$landing['blocks']" />
    </main>
@endsection

@push('head')
    @vite(['resources/css/landing.css'])
@endpush

@push('scripts')
    @vite(['resources/js/shop/landing.js'])
@endpush
