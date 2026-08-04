@extends('layouts.shop')

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

            @if ($errors->any())
                <div class="mt-6 border border-sale-DEFAULT/30 bg-sale-DEFAULT/5 px-4 py-3 text-sm text-sale-DEFAULT" role="alert">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('consultation.store', ['locale' => app()->getLocale()]) }}" class="relative mt-10 space-y-4" autocomplete="on">
                @csrf
                <input type="hidden" name="form_token" value="{{ $formToken }}">

                {{-- Honeypot for bots — keep empty --}}
                <div class="absolute -left-[9999px] top-auto h-0 w-0 overflow-hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input
                        type="text"
                        id="website"
                        name="website"
                        value=""
                        tabindex="-1"
                        autocomplete="off"
                    >
                </div>

                @if ($product)
                    <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                @endif

                <input type="text" name="name" required value="{{ old('name') }}" placeholder="{{ __('shop.consultation.name') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm @error('name') border-sale-DEFAULT @enderror">
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="{{ __('shop.consultation.email') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm @error('email') border-sale-DEFAULT @enderror">
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="{{ __('shop.consultation.phone') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm">
                <label class="block text-xs font-medium uppercase tracking-[0.14em] text-text-muted">
                    {{ __('shop.consultation.preferred_at') }}
                    <input type="datetime-local" name="preferred_at" value="{{ old('preferred_at') }}" class="mt-2 w-full border border-border-DEFAULT px-4 py-3 text-sm font-normal normal-case tracking-normal text-text-DEFAULT">
                </label>
                <textarea name="message" required rows="6" placeholder="{{ __('shop.consultation.message') }}" class="w-full border border-border-DEFAULT px-4 py-3 text-sm @error('message') border-sale-DEFAULT @enderror">{{ old('message') }}</textarea>

                @if ($turnstileSiteKey)
                    <div
                        class="cf-turnstile"
                        data-sitekey="{{ $turnstileSiteKey }}"
                        data-theme="light"
                    ></div>
                    @error('cf-turnstile-response')
                        <p class="text-sm text-sale-DEFAULT">{{ $message }}</p>
                    @enderror
                @endif

                <button type="submit" class="w-full bg-primary-DEFAULT py-3.5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                    {{ __('shop.consultation.submit') }}
                </button>
            </form>
        </div>
    </main>
@endsection

@if ($turnstileSiteKey)
    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif
