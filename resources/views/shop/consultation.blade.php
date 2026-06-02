@extends('layouts.shop')

@section('title', __('shop.consultation.title').' — '.config('shop.name'))

@section('content')
    <x-shop.header :navigation="$navigation" :languages="$languages" :cart-count="$cartCount" />

    <main class="flex-1">
        <div class="mx-auto max-w-2xl px-5 py-8 lg:px-8 lg:py-12">
            <x-shop.breadcrumbs :items="$breadcrumbs" />

            @if (session('consultation_sent'))
                <div class="mt-6 border border-status-success/30 bg-status-success/5 px-4 py-3 text-sm text-status-success">
                    {{ __('shop.consultation.success') }}
                </div>
            @endif

            <h1 class="mt-6 text-2xl font-semibold tracking-tight lg:text-3xl">{{ __('shop.consultation.title') }}</h1>
            <p class="mt-4 text-sm leading-relaxed text-text-muted">{{ __('shop.consultation.intro') }}</p>

            @if ($product)
                <p class="mt-4 rounded-lg bg-surface-muted px-4 py-3 text-sm">
                    {{ __('shop.consultation.product') }}: <strong>{{ $product['name'] }}</strong>
                </p>
            @endif

            <form method="post" action="{{ route('consultation.store', ['locale' => app()->getLocale()]) }}" class="mt-10 space-y-4">
                @csrf
                @if ($product)
                    <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                @endif

                <input type="text" name="name" required value="{{ old('name') }}" placeholder="{{ __('shop.consultation.name') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="{{ __('shop.consultation.email') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="{{ __('shop.consultation.phone') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                <input type="datetime-local" name="preferred_at" value="{{ old('preferred_at') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                <textarea name="message" required rows="6" placeholder="{{ __('shop.consultation.message') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">{{ old('message') }}</textarea>

                <button type="submit" class="w-full bg-primary-DEFAULT py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                    {{ __('shop.consultation.submit') }}
                </button>
            </form>
        </div>
    </main>
@endsection
