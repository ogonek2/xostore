@php
    $locale = app()->getLocale();

    $categoryUrl = function ($category) use ($locale) {
        $slug = $category?->translate('slug', $locale);

        return $slug
            ? route('category.show', ['locale' => $locale, 'category' => $slug])
            : '#';
    };

    $women = $menuRoots->get('women');
    $men = $menuRoots->get('men');
    $accessories = $menuRoots->get('accessories');

    $currentYear = now()->year;
@endphp

<footer class="mt-auto border-t border-border-DEFAULT/80">
    {{-- Newsletter --}}
    <div class="border-b border-border-DEFAULT/60 bg-surface-muted">
        <div class="mx-auto max-w-[90rem] px-5 py-14 lg:flex lg:items-end lg:justify-between lg:gap-16 lg:px-8 lg:py-16">
            <div class="max-w-xl">
                <p class="text-xs font-medium uppercase tracking-[0.22em] text-text-muted">
                    {{ __('shop.footer.newsletter.eyebrow') }}
                </p>
                <h2 class="mt-3 font-display text-3xl font-normal leading-tight tracking-tight text-primary-DEFAULT sm:text-4xl">
                    {{ __('shop.footer.newsletter.title') }}
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-text-muted">
                    {{ __('shop.footer.newsletter.hint') }}
                </p>
            </div>

            <form
                class="mt-8 w-full max-w-md lg:mt-0 lg:shrink-0"
                action="#"
                method="post"
                data-newsletter-form
                onsubmit="event.preventDefault(); this.querySelector('[data-newsletter-success]')?.classList.remove('hidden'); this.querySelector('[data-newsletter-fields]')?.classList.add('hidden');"
            >
                @csrf
                <div data-newsletter-fields class="flex flex-col gap-3 sm:flex-row">
                    <label class="sr-only" for="footer-newsletter-email">{{ __('shop.footer.newsletter.placeholder') }}</label>
                    <input
                        id="footer-newsletter-email"
                        type="email"
                        name="email"
                        required
                        autocomplete="email"
                        placeholder="{{ __('shop.footer.newsletter.placeholder') }}"
                        class="min-h-[3rem] flex-1 border border-border-DEFAULT bg-surface-DEFAULT px-4 text-sm text-primary-DEFAULT outline-none transition-colors placeholder:text-text-muted/70 focus:border-primary-DEFAULT"
                    >
                    <button
                        type="submit"
                        class="min-h-[3rem] shrink-0 bg-primary-DEFAULT px-8 text-sm font-medium text-text-inverse transition-colors hover:bg-primary-hover"
                    >
                        {{ __('shop.footer.newsletter.submit') }}
                    </button>
                </div>
                <p data-newsletter-success class="hidden text-sm text-status-success" role="status">
                    {{ __('shop.footer.newsletter.success') }}
                </p>
            </form>
        </div>
    </div>

    {{-- Main footer --}}
    <div class="bg-primary-DEFAULT text-text-inverse">
        <div class="mx-auto max-w-[90rem] px-5 py-14 lg:px-8 lg:py-16">
            <div class="grid gap-12 lg:grid-cols-12 lg:items-start lg:gap-8">
                <div class="lg:col-span-4">
                    <a href="{{ route('home', ['locale' => $locale]) }}" class="inline-flex leading-none" aria-label="{{ config('shop.name') }}">
                        <span class="flex flex-col text-[1.35rem] font-semibold tracking-[0.2em] uppercase text-text-inverse">
                            <span>XO</span>
                            <span>STORE</span>
                        </span>
                    </a>
                    <p class="mt-6 max-w-sm text-sm leading-relaxed text-text-inverse/65">
                        {{ __('shop.footer.tagline') }}
                    </p>

                    <div class="mt-8">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                            {{ __('shop.footer.social') }}
                        </p>
                        <div class="mt-4 flex items-center gap-3">
                            @foreach (['instagram', 'facebook', 'pinterest'] as $network)
                                @php $url = config("shop.social.{$network}"); @endphp
                                @if ($url)
                                    <a
                                        href="{{ $url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex size-10 items-center justify-center border border-text-inverse/20 text-text-inverse/80 transition-colors hover:border-text-inverse/50 hover:text-text-inverse"
                                        aria-label="{{ ucfirst($network) }}"
                                    >
                                        @if ($network === 'instagram')
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <rect x="3" y="3" width="18" height="18" rx="5" />
                                                <circle cx="12" cy="12" r="4" />
                                                <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none" />
                                            </svg>
                                        @elseif ($network === 'facebook')
                                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M14 8.5V6.75c0-.69.56-1.25 1.25-1.25H17V3h-2.25A3.75 3.75 0 0011 6.75V8.5H9v3h2V21h3v-9.5h2.25L17 11h-3z" />
                                            </svg>
                                        @else
                                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M12 3C7.03 3 3 7.03 3 12c0 4.17 2.52 7.75 6.13 9.28-.09-.79-.17-2 .03-2.84.18-.77 1.17-4.91 1.17-4.91s-.3-.6-.3-1.49c0-1.39.81-2.43 1.81-2.43.86 0 1.27.64 1.27 1.41 0 .86-.55 2.15-.83 3.35-.24 1 .5 1.82 1.48 1.82 1.78 0 3.15-1.88 3.15-4.58 0-2.39-1.72-4.06-4.18-4.06-2.85 0-4.52 2.14-4.52 4.35 0 .86.33 1.78.74 2.28a.3.3 0 01-.07.28l-.27 1.09c-.04.17-.13.21-.3.13-1.12-.52-1.82-2.15-1.82-3.47 0-2.82 2.05-5.41 5.91-5.41 3.1 0 5.51 2.21 5.51 5.17 0 3.08-1.94 5.56-4.64 5.56-.91 0-1.76-.47-2.05-1.03l-.56 2.14c-.2.78-.75 1.75-1.12 2.35.84.26 1.73.4 2.65.4 5.52 0 10-4.48 10-10S17.52 3 12 3z" />
                                            </svg>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-8 gap-y-10 sm:grid-cols-2 lg:col-span-8 lg:grid-cols-4 lg:items-start">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                            {{ __('shop.footer.columns.shop') }}
                        </p>
                        <ul class="mt-5 space-y-3">
                            <li>
                                <a href="{{ route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'new-in' : 'nowynki']) }}" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                    {{ __('shop.footer.links.new_arrivals') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'trends' : 'trendy']) }}" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                    {{ __('shop.footer.links.trending') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'promotions' : 'promocje']) }}" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                    {{ __('shop.footer.links.promotions') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('products.index', ['locale' => $locale]) }}" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                    {{ __('shop.nav.shop') }}
                                </a>
                            </li>
                            @if ($women)
                                <li>
                                    <a href="{{ $categoryUrl($women) }}" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                        {{ __('shop.footer.links.women') }}
                                    </a>
                                </li>
                            @endif
                            @if ($men)
                                <li>
                                    <a href="{{ $categoryUrl($men) }}" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                        {{ __('shop.footer.links.men') }}
                                    </a>
                                </li>
                            @endif
                            @if ($accessories)
                                <li>
                                    <a href="{{ $categoryUrl($accessories) }}" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                        {{ __('shop.footer.links.accessories') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                            {{ __('shop.footer.columns.help') }}
                        </p>
                        <ul class="mt-5 space-y-3">
                            @foreach (['shipping', 'faq', 'contact', 'authenticity'] as $link)
                                <li>
                                    <a href="#" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                        {{ __('shop.footer.links.'.$link) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                            {{ __('shop.footer.columns.brand') }}
                        </p>
                        <ul class="mt-5 space-y-3">
                            @foreach (['about', 'stores', 'careers'] as $link)
                                <li>
                                    <a href="#" class="text-sm text-text-inverse/75 transition-colors hover:text-text-inverse">
                                        {{ __('shop.footer.links.'.$link) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                            {{ __('shop.footer.contact.title') }}
                        </p>
                        <ul class="mt-5 space-y-4 text-sm">
                            <li>
                                <span class="block text-text-inverse/45">{{ __('shop.footer.contact.email') }}</span>
                                <a href="mailto:{{ config('shop.contact.email') }}" class="mt-1 inline-block break-all text-text-inverse/80 transition-colors hover:text-text-inverse">
                                    {{ config('shop.contact.email') }}
                                </a>
                            </li>
                            <li>
                                <span class="block text-text-inverse/45">{{ __('shop.footer.contact.phone') }}</span>
                                <a href="tel:{{ preg_replace('/\s+/', '', config('shop.contact.phone')) }}" class="mt-1 inline-block text-text-inverse/80 transition-colors hover:text-text-inverse">
                                    {{ config('shop.contact.phone') }}
                                </a>
                            </li>
                        </ul>

                        <p class="mt-8 text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/50">
                            {{ __('shop.footer.payments') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['Visa', 'Mastercard', 'BLIK', 'Apple Pay'] as $method)
                                <span class="border border-text-inverse/15 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wider text-text-inverse/55">
                                    {{ $method }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-14 flex flex-col gap-6 border-t border-text-inverse/10 pt-8 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-xs text-text-inverse/45">
                    &copy; {{ $currentYear }} {{ config('shop.name') }}. {{ __('shop.footer.copyright') }}
                </p>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                    <a href="#" class="text-xs text-text-inverse/55 transition-colors hover:text-text-inverse">
                        {{ __('shop.footer.links.privacy') }}
                    </a>
                    <a href="#" class="text-xs text-text-inverse/55 transition-colors hover:text-text-inverse">
                        {{ __('shop.footer.links.terms') }}
                    </a>

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
        </div>
    </div>
</footer>
