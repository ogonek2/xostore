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
])

@php
    $isMinimal = $variant === 'minimal';
@endphp

<article {{ $attributes->class(['group flex w-full flex-col']) }}>
    <a href="{{ $url }}" class="flex flex-col">
        <div @class([
            'relative overflow-hidden bg-[#eceae6]',
            'aspect-[4/5] rounded-t-2xl' => ! $isMinimal,
            'aspect-[3/4] rounded-2xl' => $isMinimal,
        ])>
            <img
                src="{{ $image }}"
                alt="{{ $alt ?: $name }}"
                class="size-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.02]"
                loading="lazy"
            >

            @if ($showNewBadge)
                <span class="absolute left-3 top-3 bg-surface-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary-DEFAULT">
                    {{ __('shop.new_arrivals.badge') }}
                </span>
            @endif

            <button
                type="button"
                @class([
                    'absolute flex size-9 items-center justify-center rounded-full bg-white text-primary-DEFAULT shadow-sm transition-transform hover:scale-105',
                    'left-3 top-3' => ! $isMinimal,
                    'right-3 top-3' => $isMinimal,
                ])
                aria-label="{{ __('shop.cart') }}"
                onclick="event.preventDefault(); event.stopPropagation(); window.dispatchEvent(new Event('cart:open'));"
            >
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M6 6h15l-1.5 9h-12L6 6z" stroke-linejoin="round" />
                    <path d="M6 6L5 3H2" stroke-linecap="round" />
                    <circle cx="9" cy="20" r="1" fill="currentColor" stroke="none" />
                    <circle cx="18" cy="20" r="1" fill="currentColor" stroke="none" />
                </svg>
            </button>
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
