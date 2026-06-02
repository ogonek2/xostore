<div data-shop-nav class="sticky top-0 z-50">
    <header class="border-b border-border-DEFAULT/60 bg-surface-DEFAULT/95 backdrop-blur-sm">
        <div class="mx-auto flex h-[4.5rem] max-w-[90rem] items-center gap-4 px-5 lg:gap-6 lg:px-8">
            <x-shop.logo />

            @if (! empty($navigation))
                <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex" aria-label="{{ config('shop.name') }}">
                    @foreach ($navigation as $item)
                        <x-shop.nav-item :item="$item" />
                    @endforeach
                </nav>
            @endif

            <div class="ml-auto flex items-center gap-2 lg:gap-4">
                @if (! empty($navigation))
                    <button
                        type="button"
                        class="inline-flex size-10 items-center justify-center text-text-DEFAULT lg:hidden"
                        data-mobile-nav-toggle
                        aria-expanded="false"
                        aria-label="{{ __('shop.nav.open_menu') }}"
                    >
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                        </svg>
                    </button>
                @endif

                <div
                    class="relative"
                    data-header-search
                    data-search-endpoint="{{ route('api.products.index', ['locale' => app()->getLocale()]) }}"
                    data-search-results-url="{{ route('products.index', ['locale' => app()->getLocale()]) }}"
                >
                    <button
                        type="button"
                        class="flex size-10 items-center justify-center text-text-DEFAULT transition-colors hover:text-text-muted"
                        aria-label="{{ __('shop.search') }}"
                        data-search-toggle
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M20 20L16 16" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div
                        class="absolute right-0 top-12 z-50 hidden w-[min(92vw,25rem)] rounded-xl border border-border-DEFAULT bg-surface-DEFAULT p-3 shadow-xl"
                        data-search-panel
                    >
                        <form method="GET" action="{{ route('products.index', ['locale' => app()->getLocale()]) }}" data-search-form>
                            <label class="sr-only" for="header-search">{{ __('shop.search') }}</label>
                            <input
                                id="header-search"
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="{{ __('shop.listing.search_placeholder') }}"
                                autocomplete="off"
                                class="h-11 w-full border border-border-DEFAULT px-3 text-sm outline-none transition focus:border-primary-DEFAULT"
                                data-search-input
                            >
                        </form>

                        <div class="mt-3 hidden text-xs text-text-muted" data-search-loading>{{ __('shop.listing.loading') }}</div>
                        <ul class="mt-3 hidden max-h-80 space-y-2 overflow-auto" data-search-results></ul>
                        <p class="mt-3 hidden text-sm text-text-muted" data-search-empty>{{ __('shop.listing.empty') }}</p>
                    </div>

                    <div class="fixed inset-0 z-[100] hidden lg:hidden" data-search-mobile-overlay>
                        <div class="absolute inset-0 bg-black/40" data-search-mobile-close></div>
                        <div class="relative z-[1] h-full bg-surface-DEFAULT">
                            <div class="mx-auto flex h-full max-w-[90rem] flex-col px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <form method="GET" action="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="flex-1" data-search-mobile-form>
                                        <label class="sr-only" for="header-search-mobile">{{ __('shop.search') }}</label>
                                        <input
                                            id="header-search-mobile"
                                            type="search"
                                            name="q"
                                            value="{{ request('q') }}"
                                            placeholder="{{ __('shop.listing.search_placeholder') }}"
                                            autocomplete="off"
                                            class="h-11 w-full border border-border-DEFAULT px-3 text-sm outline-none transition focus:border-primary-DEFAULT"
                                            data-search-mobile-input
                                        >
                                    </form>
                                    <button
                                        type="button"
                                        class="inline-flex h-11 items-center justify-center border border-border-DEFAULT px-4 text-sm font-medium text-text-DEFAULT"
                                        data-search-mobile-close
                                    >
                                        {{ __('shop.product.gallery_close') }}
                                    </button>
                                </div>
                                <div class="mt-4 hidden text-xs text-text-muted" data-search-mobile-loading>{{ __('shop.listing.loading') }}</div>
                                <ul class="mt-4 flex-1 space-y-2 overflow-auto" data-search-mobile-results></ul>
                                <p class="mt-4 hidden text-sm text-text-muted" data-search-mobile-empty>{{ __('shop.listing.empty') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="relative flex size-10 items-center justify-center text-text-DEFAULT transition-colors hover:text-text-muted"
                    aria-label="{{ __('shop.cart.label') }}"
                    onclick="window.dispatchEvent(new Event('cart:open'))"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path d="M6 6h15l-1.5 9h-12L6 6z" stroke-linejoin="round" />
                        <path d="M6 6L5 3H2" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="9" cy="20" r="1" fill="currentColor" stroke="none" />
                        <circle cx="18" cy="20" r="1" fill="currentColor" stroke="none" />
                    </svg>
                    <span
                        data-cart-count
                        @class([
                            'absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-primary-DEFAULT text-[10px] font-semibold text-text-inverse',
                            'hidden' => $cartCount < 1,
                        ])
                    >{{ $cartCount }}</span>
                </button>

                <x-shop.language-switcher :languages="$languages" />
            </div>
        </div>
    </header>

    @if ($megaItems !== [])
        <div
            data-mega-layer
            data-open="false"
            class="absolute inset-x-0 top-full hidden border-b border-border-DEFAULT bg-surface-DEFAULT shadow-lg"
        >
            <div class="mx-auto max-w-[90rem] px-5 py-8 lg:px-8">
                @foreach ($megaItems as $index => $megaItem)
                    <div
                        data-mega-panel="{{ $index }}"
                        class="hidden"
                        role="region"
                        aria-label="{{ $megaItem['label'] }}"
                    >
                        <x-shop.nav-mega-panels :panels="$megaItem['panels']" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (! empty($navigation))
        <div
            data-mobile-nav-drawer
            class="fixed inset-0 z-[70] hidden lg:hidden"
            aria-hidden="true"
        >
            <div class="absolute inset-0 bg-black/40" data-mobile-nav-close aria-hidden="true"></div>

            <div class="relative ml-auto flex h-full w-[min(100%,22rem)] flex-col bg-surface-DEFAULT shadow-2xl">
                <div class="flex items-center justify-between border-b border-border-DEFAULT px-5 py-3.5">
                    <span class="text-sm font-semibold tracking-wide text-text-DEFAULT">{{ config('shop.name') }}</span>
                    <button
                        type="button"
                        class="inline-flex size-10 items-center justify-center text-text-DEFAULT"
                        data-mobile-nav-close
                        aria-label="{{ __('shop.nav.close_menu') }}"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4">
                    @if ($mobileUtilityLinks !== [])
                        <ul class="divide-y divide-border-DEFAULT/80">
                            @foreach ($mobileUtilityLinks as $link)
                                <li>
                                    @if (! empty($link['action']))
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between py-3.5 text-left text-sm font-medium text-text-DEFAULT"
                                            data-mobile-nav-action="{{ $link['action'] }}"
                                        >
                                            <span>{{ $link['label'] }}</span>
                                            @if (($link['badge'] ?? 0) > 0)
                                                <span class="flex size-5 items-center justify-center rounded-full bg-primary-DEFAULT text-[10px] font-semibold text-text-inverse">
                                                    {{ $link['badge'] }}
                                                </span>
                                            @endif
                                        </button>
                                    @else
                                        <a
                                            href="{{ $link['url'] }}"
                                            class="block py-3.5 text-sm font-medium text-text-DEFAULT"
                                            data-mobile-nav-link
                                            @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                                        >
                                            {{ $link['label'] }}
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($catalogMegaItems !== [])
                        <div class="mt-2 border-t border-border-DEFAULT pt-2">
                            <button
                                type="button"
                                data-mobile-catalog-toggle
                                aria-expanded="false"
                                class="flex w-full items-center justify-between py-3.5 text-left text-sm font-medium text-text-DEFAULT"
                            >
                                <span>{{ __('shop.nav.catalog') }}</span>
                                <svg class="size-4 shrink-0 text-text-muted transition-transform" data-mobile-catalog-icon viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div data-mobile-catalog-panel class="hidden border-t border-border-DEFAULT/60 pb-4 pt-3">
                                <x-shop.nav-mega-mobile :mega-items="$catalogMegaItems" />
                            </div>
                        </div>
                    @endif

                    @foreach ($simpleNavItems->filter(fn ($item) => ! empty($item['children'])) as $item)
                        <div class="mt-2 border-t border-border-DEFAULT pt-2">
                            <details class="group">
                                <summary class="flex cursor-pointer list-none items-center justify-between py-3.5 text-sm font-medium text-text-DEFAULT [&::-webkit-details-marker]:hidden">
                                    {{ $item['label'] }}
                                    <svg class="size-4 text-text-muted transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                    </svg>
                                </summary>
                                <ul class="mb-2 space-y-0.5 border-l border-border-DEFAULT/60 pl-3">
                                    @foreach ($item['children'] as $child)
                                        <li>
                                            <a
                                                href="{{ $child['url'] ?? '#' }}"
                                                class="block py-2 text-sm text-text-muted"
                                                data-mobile-nav-link
                                            >
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-header-search]').forEach((root) => {
                    const toggle = root.querySelector('[data-search-toggle]');
                    const panel = root.querySelector('[data-search-panel]');
                    const form = root.querySelector('[data-search-form]');
                    const input = root.querySelector('[data-search-input]');
                    const list = root.querySelector('[data-search-results]');
                    const loading = root.querySelector('[data-search-loading]');
                    const empty = root.querySelector('[data-search-empty]');
                    const mobileOverlay = root.querySelector('[data-search-mobile-overlay]');
                    const mobileForm = root.querySelector('[data-search-mobile-form]');
                    const mobileInput = root.querySelector('[data-search-mobile-input]');
                    const mobileList = root.querySelector('[data-search-mobile-results]');
                    const mobileLoading = root.querySelector('[data-search-mobile-loading]');
                    const mobileEmpty = root.querySelector('[data-search-mobile-empty]');
                    const mobileCloseButtons = root.querySelectorAll('[data-search-mobile-close]');
                    const endpoint = root.dataset.searchEndpoint;
                    const resultsUrl = root.dataset.searchResultsUrl;
                    let debounceId;
                    let mobileDebounceId;
                    const isMobile = () => window.matchMedia('(max-width: 1023px)').matches;

                    if (mobileOverlay && mobileOverlay.parentElement !== document.body) {
                        document.body.appendChild(mobileOverlay);
                    }

                    const closeDesktopPanel = () => panel?.classList.add('hidden');
                    const closeMobileOverlay = () => {
                        mobileOverlay?.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    };
                    const openDesktopPanel = () => {
                        panel?.classList.remove('hidden');
                        setTimeout(() => input?.focus(), 0);
                    };
                    const openMobileOverlay = () => {
                        mobileOverlay?.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                        setTimeout(() => mobileInput?.focus(), 0);
                    };

                    const renderResults = (items, targetList, targetEmpty) => {
                        if (!targetList) return;
                        targetList.innerHTML = '';
                        items.forEach((item) => {
                            const li = document.createElement('li');
                            li.innerHTML = `
                                <a href="${item.url}" class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-surface-muted">
                                    <img src="${item.image}" alt="${item.alt ?? item.name}" class="h-14 w-12 rounded-md object-cover object-center bg-[#eceae6]" loading="lazy">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-primary-DEFAULT">${item.name}</span>
                                        ${item.price_formatted ? `<span class="mt-0.5 block text-sm text-text-muted">${item.price_formatted}</span>` : ''}
                                    </span>
                                </a>
                            `;
                            targetList.appendChild(li);
                        });
                        targetList.classList.toggle('hidden', items.length === 0);
                        targetEmpty?.classList.toggle('hidden', items.length > 0);
                    };

                    const runSearch = async (query, options = {}) => {
                        const { allowEmpty = false, target = 'desktop' } = options;
                        const targetList = target === 'mobile' ? mobileList : list;
                        const targetLoading = target === 'mobile' ? mobileLoading : loading;
                        const targetEmpty = target === 'mobile' ? mobileEmpty : empty;
                        const q = query.trim();

                        if (!allowEmpty && q.length < 2) {
                            targetList?.classList.add('hidden');
                            targetEmpty?.classList.add('hidden');
                            targetLoading?.classList.add('hidden');
                            return;
                        }

                        targetLoading?.classList.remove('hidden');
                        targetList?.classList.add('hidden');
                        targetEmpty?.classList.add('hidden');

                        try {
                            const queryPart = q ? `q=${encodeURIComponent(q)}&` : '';
                            const response = await fetch(`${endpoint}?${queryPart}per_page=6`, {
                                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            if (!response.ok) throw new Error('Search failed');
                            const payload = await response.json();
                            renderResults(payload?.data ?? [], targetList, targetEmpty);
                        } catch {
                            targetList?.classList.add('hidden');
                            targetEmpty?.classList.remove('hidden');
                        } finally {
                            targetLoading?.classList.add('hidden');
                        }
                    };

                    toggle?.addEventListener('click', () => {
                        if (isMobile()) {
                            mobileOverlay?.classList.contains('hidden') ? (openMobileOverlay(), runSearch('', { allowEmpty: true, target: 'mobile' })) : closeMobileOverlay();
                        } else {
                            panel?.classList.contains('hidden') ? openDesktopPanel() : closeDesktopPanel();
                        }
                    });

                    form?.addEventListener('submit', (e) => {
                        e.preventDefault();
                        const q = input?.value?.trim() ?? '';
                        window.location.href = q ? `${resultsUrl}?q=${encodeURIComponent(q)}` : resultsUrl;
                    });
                    input?.addEventListener('input', () => {
                        clearTimeout(debounceId);
                        debounceId = setTimeout(() => runSearch(input.value), 250);
                    });
                    mobileForm?.addEventListener('submit', (e) => {
                        e.preventDefault();
                        const q = mobileInput?.value?.trim() ?? '';
                        window.location.href = q ? `${resultsUrl}?q=${encodeURIComponent(q)}` : resultsUrl;
                    });
                    mobileInput?.addEventListener('input', () => {
                        clearTimeout(mobileDebounceId);
                        mobileDebounceId = setTimeout(() => runSearch(mobileInput.value, { allowEmpty: true, target: 'mobile' }), 250);
                    });
                    mobileCloseButtons.forEach((btn) => btn.addEventListener('click', closeMobileOverlay));
                    document.addEventListener('click', (e) => {
                        if (!root.contains(e.target)) closeDesktopPanel();
                    });
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') { closeDesktopPanel(); closeMobileOverlay(); }
                    });
                });
            });
        </script>
    @endpush
@endonce
