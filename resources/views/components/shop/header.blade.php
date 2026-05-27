@php
    $locale = app()->getLocale();

    $categoryChildren = function ($category) use ($locale) {
        return $category->children->map(fn ($child) => [
            'label' => $child->translate('name', $locale),
            'url' => route('category.show', [
                'locale' => $locale,
                'category' => $child->translate('slug', $locale) ?? $child->code,
            ]),
        ])->all();
    };

    $women = $menuRoots->get('women');
    $men = $menuRoots->get('men');
    $accessories = $menuRoots->get('accessories');
@endphp

<header class="sticky top-0 z-50 border-b border-border-DEFAULT/60 bg-surface-DEFAULT/95 backdrop-blur-sm">
    <div class="mx-auto flex h-[4.5rem] max-w-[90rem] items-center gap-6 px-5 lg:px-8">
        <x-shop.logo />

        <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex" aria-label="{{ __('shop.nav.shop') }}">
            <a href="{{ route('products.index', ['locale' => $locale]) }}" class="text-sm font-medium text-text-DEFAULT transition-colors hover:text-text-muted">
                {{ __('shop.nav.shop') }}
            </a>
            <a href="{{ route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'promotions' : 'promocje']) }}" class="text-sm font-medium text-text-DEFAULT transition-colors hover:text-text-muted">
                {{ __('shop.nav.promotions') }}
            </a>
            <a href="{{ route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'trends' : 'trendy']) }}" class="text-sm font-medium text-text-DEFAULT transition-colors hover:text-text-muted">
                {{ __('shop.nav.trends') }}
            </a>
            <a href="{{ route('catalog.show', ['locale' => $locale, 'catalog' => $locale === 'en' ? 'in-stock' : 'w-magazynie']) }}" class="text-sm font-medium text-text-DEFAULT transition-colors hover:text-text-muted">
                {{ __('shop.nav.in_stock') }}
            </a>

            @if ($women)
                <x-shop.nav-dropdown
                    :label="__('shop.nav.women')"
                    :items="$categoryChildren($women)"
                />
            @else
                <a href="#" class="text-sm font-medium text-text-DEFAULT">{{ __('shop.nav.women') }}</a>
            @endif

            @if ($men)
                <x-shop.nav-dropdown
                    :label="__('shop.nav.men')"
                    :items="$categoryChildren($men)"
                />
            @else
                <a href="#" class="text-sm font-medium text-text-DEFAULT">{{ __('shop.nav.men') }}</a>
            @endif

            @if ($accessories)
                <x-shop.nav-dropdown
                    :label="__('shop.nav.accessories')"
                    :items="$categoryChildren($accessories)"
                />
            @else
                <a href="#" class="text-sm font-medium text-text-DEFAULT">{{ __('shop.nav.accessories') }}</a>
            @endif

            <a href="{{ route('consultation.show', ['locale' => $locale]) }}" class="text-sm font-medium text-text-DEFAULT transition-colors hover:text-text-muted">
                {{ __('shop.nav.contact') }}
            </a>
        </nav>

        <div class="ml-auto flex items-center gap-3 lg:gap-4">
            <button
                type="button"
                class="flex size-10 items-center justify-center text-text-DEFAULT transition-colors hover:text-text-muted"
                aria-label="{{ __('shop.search') }}"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M20 20L16 16" stroke-linecap="round" />
                </svg>
            </button>

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
