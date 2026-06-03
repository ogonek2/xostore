<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('shop.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-surface-muted px-5 font-sans text-text-DEFAULT antialiased">
    <div class="w-full max-w-md border border-border-DEFAULT bg-surface-DEFAULT p-8 text-center shadow-sm">
        @if ($success)
            <h1 class="text-xl font-semibold text-primary-DEFAULT">{{ __('shop.newsletter.unsubscribe.title') }}</h1>
            <p class="mt-3 text-sm text-text-muted">
                {{ __('shop.newsletter.unsubscribe.message', ['email' => $email]) }}
            </p>
        @else
            <h1 class="text-xl font-semibold text-primary-DEFAULT">{{ __('shop.newsletter.unsubscribe.invalid_title') }}</h1>
            <p class="mt-3 text-sm text-text-muted">{{ __('shop.newsletter.unsubscribe.invalid_message') }}</p>
        @endif
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="mt-6 inline-block text-sm font-medium text-primary-DEFAULT underline">
            {{ __('shop.newsletter.unsubscribe.back') }}
        </a>
    </div>
</body>
</html>
