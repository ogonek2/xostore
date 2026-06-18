@props([
    'product' => [],
])

<a
    href="{{ $product['url'] }}"
    class="mega-product group/card flex w-[6.5rem] shrink-0 flex-col sm:w-[7rem]"
>
    <div class="relative size-[6.5rem] overflow-hidden rounded bg-[#eceae6] sm:size-[7rem]">
        <img
            src="{{ $product['image'] }}"
            alt="{{ $product['name'] }}"
            class="size-full object-cover object-center transition-transform duration-500 group-hover/card:scale-[1.05]"
            loading="lazy"
            width="112"
            height="112"
        >
        @if ($product['on_sale'] ?? false)
            <span class="absolute left-1 top-1 bg-sale-DEFAULT px-1 py-px text-[8px] font-bold uppercase tracking-wide text-white">
                {{ __('shop.nav.promo_badge') }}
            </span>
        @endif
    </div>

    <p class="mt-1.5 h-7 overflow-hidden text-[10px] font-medium leading-[0.875rem] text-primary-DEFAULT line-clamp-2">
        {{ $product['name'] }}
    </p>

    <div class="mt-0.5 flex h-3.5 items-baseline gap-1 overflow-hidden">
        @if (! empty($product['price_formatted']))
            @if (! empty($product['compare_at_formatted']))
                <span class="shrink-0 text-[9px] text-text-muted line-through">{{ $product['compare_at_formatted'] }}</span>
            @endif
            <span @class([
                'truncate text-[10px] font-semibold leading-none',
                'text-sale-DEFAULT' => $product['on_sale'] ?? false,
                'text-primary-DEFAULT' => ! ($product['on_sale'] ?? false),
            ])>{{ $product['price_formatted'] }}</span>
        @endif
    </div>
</a>
