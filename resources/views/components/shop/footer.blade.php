@props([
    'footer',
    'languages',
])

@php
    $locale = app()->getLocale();
    $newsletter = $footer['newsletter'] ?? null;
    $brand = $footer['brand'] ?? null;
    $social = $footer['social'] ?? null;
    $columns = $footer['columns'] ?? [];
    $contact = $footer['contact'] ?? null;
    $payments = $footer['payments'] ?? null;
    $bottom = $footer['bottom'] ?? ['copyright' => null, 'links' => []];
    $currentYear = $footer['current_year'] ?? now()->year;

    $hasMain = $brand || $social || $columns !== [] || $contact || $payments;
@endphp

<footer class="mt-auto border-t border-border-DEFAULT/80">
    @if ($newsletter)
        <div class="border-b border-border-DEFAULT/60 bg-surface-muted">
            <div class="mx-auto max-w-[90rem] px-5 py-14 lg:flex lg:items-end lg:justify-between lg:gap-16 lg:px-8 lg:py-16">
                <div class="max-w-xl">
                    @if ($newsletter['eyebrow'] ?? null)
                        <p class="text-xs font-medium uppercase tracking-[0.22em] text-text-muted">
                            {{ $newsletter['eyebrow'] }}
                        </p>
                    @endif
                    @if ($newsletter['title'] ?? null)
                        <h2 class="mt-3 font-display text-3xl font-normal leading-tight tracking-tight text-primary-DEFAULT sm:text-4xl">
                            {{ $newsletter['title'] }}
                        </h2>
                    @endif
                    @if ($newsletter['hint'] ?? null)
                        <p class="mt-4 text-sm leading-relaxed text-text-muted">
                            {{ $newsletter['hint'] }}
                        </p>
                    @endif
                </div>

                <form
                    class="mt-8 w-full max-w-md lg:mt-0 lg:shrink-0"
                    method="post"
                    data-newsletter-form
                    data-newsletter-endpoint="{{ $newsletter['endpoint'] }}"
                    data-newsletter-error="{{ $newsletter['error'] ?? '' }}"
                >
                    <div data-newsletter-fields class="flex flex-col gap-3 sm:flex-row">
                        <label class="sr-only" for="footer-newsletter-email">{{ $newsletter['placeholder'] ?? 'E-mail' }}</label>
                        <input
                            id="footer-newsletter-email"
                            type="email"
                            name="email"
                            required
                            autocomplete="email"
                            data-newsletter-email
                            placeholder="{{ $newsletter['placeholder'] ?? '' }}"
                            class="min-h-[3rem] flex-1 border border-border-DEFAULT bg-surface-DEFAULT px-4 text-sm text-primary-DEFAULT outline-none transition-colors placeholder:text-text-muted/70 focus:border-primary-DEFAULT"
                        >
                        <button
                            type="submit"
                            data-newsletter-submit
                            class="min-h-[3rem] shrink-0 bg-primary-DEFAULT px-8 text-sm font-medium text-text-inverse transition-colors hover:bg-primary-hover disabled:opacity-60"
                        >
                            {{ $newsletter['submit'] ?? '' }}
                        </button>
                    </div>
                    <p data-newsletter-error class="mt-3 hidden text-sm text-sale-DEFAULT" role="alert"></p>
                    @if ($newsletter['success'] ?? null)
                        <p data-newsletter-success class="mt-3 hidden text-sm text-status-success" role="status">
                            {{ $newsletter['success'] }}
                        </p>
                    @endif
                </form>
            </div>
        </div>
    @endif

    @if ($hasMain)
        <div class="bg-primary-DEFAULT text-text-inverse">
            <div class="mx-auto max-w-[90rem] px-5 py-14 lg:px-8 lg:py-16">
                <div class="grid gap-12 lg:grid-cols-12 lg:items-start lg:gap-8">
                    @if ($brand)
                        <div class="lg:col-span-4">
                            <a href="{{ $brand['home_url'] }}" class="inline-flex leading-none" aria-label="{{ $brand['shop_name'] }}">
                                <span class="flex flex-col text-[1.35rem] font-semibold tracking-[0.2em] uppercase text-text-inverse">
                                    <span>XO</span>
                                    <span>STORE</span>
                                </span>
                            </a>
                            @if ($brand['tagline'] ?? null)
                                <p class="mt-6 max-w-sm text-sm leading-relaxed text-text-inverse/65">
                                    {{ $brand['tagline'] }}
                                </p>
                            @endif

                            @if ($social && ($social['links'] ?? []) !== [])
                                <div class="mt-8">
                                    @if ($social['title'] ?? null)
                                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                                            {{ $social['title'] }}
                                        </p>
                                    @endif
                                    <div @class(['flex items-center gap-3', 'mt-4' => ! empty($social['title'])])>
                                        @foreach ($social['links'] as $link)
                                            <a
                                                href="{{ $link['url'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="flex size-10 items-center justify-center border border-text-inverse/20 text-text-inverse/80 transition-colors hover:border-text-inverse/50 hover:text-text-inverse"
                                                aria-label="{{ $link['label'] }}"
                                            >
                                                <x-shop.social-icon :network="$link['network']" />
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($columns !== [] || $contact || $payments)
                        <div @class([
                            'grid gap-x-8 gap-y-10 sm:grid-cols-2',
                            'lg:col-span-8 lg:grid-cols-4 lg:items-start' => $brand,
                            'lg:col-span-12 lg:grid-cols-4' => ! $brand,
                        ])>
                            @foreach ($columns as $column)
                                <div>
                                    @if ($column['title'] ?? null)
                                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                                            {{ $column['title'] }}
                                        </p>
                                    @endif
                                    @if (($column['links'] ?? []) !== [])
                                        <ul class="mt-5 space-y-3">
                                            @foreach ($column['links'] as $link)
                                                <li>
                                                    <a
                                                        href="{{ $link['url'] }}"
                                                        @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                                                        class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse"
                                                    >
                                                        {{ $link['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach

                            @if ($contact)
                                <div>
                                    @if ($contact['title'] ?? null)
                                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                                            {{ $contact['title'] }}
                                        </p>
                                    @endif
                                    <ul class="mt-5 space-y-4 text-sm">
                                        @if ($contact['email'] ?? null)
                                            <li>
                                                @if ($contact['email_label'] ?? null)
                                                    <span class="block text-text-inverse/45">{{ $contact['email_label'] }}</span>
                                                @endif
                                                <a href="mailto:{{ $contact['email'] }}" class="mt-1 inline-block break-all text-text-inverse/80 transition-colors hover:text-text-inverse">
                                                    {{ $contact['email'] }}
                                                </a>
                                            </li>
                                        @endif
                                        @if ($contact['phone'] ?? null)
                                            <li>
                                                @if ($contact['phone_label'] ?? null)
                                                    <span class="block text-text-inverse/45">{{ $contact['phone_label'] }}</span>
                                                @endif
                                                <a href="tel:{{ $contact['phone_href'] }}" class="mt-1 inline-block text-text-inverse/80 transition-colors hover:text-text-inverse">
                                                    {{ $contact['phone'] }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>

                                    @if ($payments && ($payments['methods'] ?? []) !== [])
                                        <div class="mt-8">
                                            @if ($payments['title'] ?? null)
                                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                                                    {{ $payments['title'] }}
                                                </p>
                                            @endif
                                            <div @class(['flex flex-wrap gap-2', 'mt-3' => ! empty($payments['title'])])>
                                                @foreach ($payments['methods'] as $method)
                                                    <span class="border border-text-inverse/15 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wider text-text-inverse/55">
                                                        {{ $method }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif ($payments && ($payments['methods'] ?? []) !== [])
                                <div>
                                    @if ($payments['title'] ?? null)
                                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                                            {{ $payments['title'] }}
                                        </p>
                                    @endif
                                    <div @class(['flex flex-wrap gap-2', 'mt-3' => ! empty($payments['title'])])>
                                        @foreach ($payments['methods'] as $method)
                                            <span class="border border-text-inverse/15 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wider text-text-inverse/55">
                                                {{ $method }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                @if (($bottom['copyright'] ?? null) || ($bottom['links'] ?? []) !== [] || $languages->count() > 1)
                    <div class="mt-14 flex flex-col gap-6 border-t border-text-inverse/10 pt-8 lg:flex-row lg:items-center lg:justify-between">
                        <p class="text-xs text-text-inverse/45">
                            &copy; {{ $currentYear }} {{ config('shop.name') }}@if ($bottom['copyright'] ?? null). {{ $bottom['copyright'] }}@endif
                        </p>

                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                            @foreach ($bottom['links'] ?? [] as $link)
                                <a
                                    href="{{ $link['url'] }}"
                                    @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                                    class="text-xs text-text-inverse/55 transition-colors hover:text-text-inverse"
                                >
                                    {{ $link['label'] }}
                                </a>
                            @endforeach

                            @if ($languages->count() > 1)
                                <div class="flex items-center gap-3 border-l border-text-inverse/15 pl-6">
                                    @foreach ($languages as $language)
                                        <a
                                            href="{{ route('home', ['locale' => $language->code]) }}"
                                            @class([
                                                'text-xs uppercase tracking-wider transition-colors',
                                                'text-text-inverse font-medium' => $language->code === $locale,
                                                'text-text-inverse/50 hover:text-text-inverse' => $language->code !== $locale,
                                            ])
                                        >
                                            {{ strtoupper($language->code) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</footer>
