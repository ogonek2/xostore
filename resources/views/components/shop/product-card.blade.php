@props([
    'url' => '#',
    'name',
    'category' => null,
    'priceFormatted' => null,
    'image',
    'colors' => [],
    'alt' => '',
    'variant' => 'default',
    'showNewBadge' => false,
    'productId' => null,
])

@php
    $isMinimal = $variant === 'minimal';
@endphp

<article
    @if ($productId) data-product-id="{{ $productId }}" @endif
    data-product-card
    data-in-cart-label="{{ __('shop.cart.in_cart_label', ['name' => $name]) }}"
    {{ $attributes->class([
        'group/product-card flex min-w-0 max-w-full flex-col',
    ]) }}
>
    <a href="{{ $url }}" class="flex flex-col">
        <div @class([
            'relative overflow-hidden bg-[#eceae6]',
            'aspect-[4/5] rounded-t-2xl' => ! $isMinimal,
            'aspect-[3/4] rounded-2xl' => $isMinimal,
            'ring-inset ring-1 ring-transparent group-[.is-in-cart]/product-card:ring-primary-DEFAULT' => ! $isMinimal,
        ])>
            <img
                src="{{ $image }}"
                alt="{{ $alt ?: $name }}"
                class="size-full object-cover object-center transition-transform duration-700 ease-out group-hover/product-card:scale-[1.02]"
                loading="lazy"
            >

            @if ($showNewBadge)
                <span class="absolute left-3 top-3 z-[1] bg-surface-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary-DEFAULT">
                    {{ __('shop.new_arrivals.badge') }}
                </span>
            @endif

            @unless ($isMinimal)
                <span
                    class="pointer-events-none absolute bottom-3 left-3 right-3 z-[2] hidden items-center justify-center gap-1.5 bg-primary-DEFAULT px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-text-inverse group-[.is-in-cart]/product-card:flex"
                >
                    <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    {{ __('shop.cart.in_cart') }}
                </span>
            @endunless

            <button
                type="button"
                @class([
                    'absolute z-[2] flex size-9 items-center justify-center rounded-full bg-white text-primary-DEFAULT shadow-sm transition-transform hover:scale-105 group-[.is-in-cart]/product-card:hidden',
                    'left-3 top-3' => ! $isMinimal,
                    'right-3 top-3' => $isMinimal,
                ])
                aria-label="{{ __('shop.cart.label') }}"
                onclick="event.preventDefault(); event.stopPropagation(); window.dispatchEvent(new Event('cart:open'));"
            >
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M6 6h15l-1.5 9h-12L6 6z" stroke-linejoin="round" />
                    <path d="M6 6L5 3H2" stroke-linecap="round" />
                    <circle cx="9" cy="20" r="1" fill="currentColor" stroke="none" />
                    <circle cx="18" cy="20" r="1" fill="currentColor" stroke="none" />
                </svg>
            </button>

            <span
                @class([
                    'absolute z-[2] hidden size-9 items-center justify-center rounded-full bg-primary-DEFAULT text-text-inverse group-[.is-in-cart]/product-card:flex',
                    'left-3 top-3' => ! $isMinimal,
                    'right-3 top-3' => $isMinimal,
                ])
                aria-hidden="true"
            >
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </div>

        <div @class(['pt-4' => ! $isMinimal, 'pt-3' => $isMinimal])>
            <h3 @class([
                'font-semibold leading-snug tracking-tight text-primary-DEFAULT',
                'text-[0.95rem]' => ! $isMinimal,
                'text-sm text-text-muted font-normal' => $isMinimal,
            ])>
                {{ $name }}
            </h3>

            @if ($category && ! $isMinimal)
                <p class="mt-1 text-sm text-text-muted">
                    {{ $category }}
                </p>
            @endif

            @if ($priceFormatted)
                <p @class([
                    'font-semibold text-primary-DEFAULT',
                    'mt-2 text-[0.95rem]' => ! $isMinimal,
                    'mt-1.5 text-[0.95rem]' => $isMinimal,
                ])>
                    {{ $priceFormatted }}
                </p>
            @endif
        </div>
    </a>

    @if (count($colors) > 0 && ! $isMinimal)
        <div class="mt-3 flex flex-wrap items-center gap-2" role="list" aria-label="{{ __('shop.product.colors') }}">
            @foreach ($colors as $hex)
                <span
                    role="listitem"
                    class="size-5 rounded-full border border-border-DEFAULT"
                    style="background-color: {{ $hex }}"
                    title="{{ $hex }}"
                ></span>
            @endforeach
        </div>
    @endif
</article>
