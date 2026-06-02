@props([
    'panels' => [],
])

@php
    $hasProducts = collect($panels)->contains(fn ($panel) => ! empty($panel['products']));
@endphp

<div @class([
    'grid gap-8',
    'lg:grid-cols-12' => $hasProducts,
    'lg:grid-cols-[repeat(auto-fit,minmax(10rem,1fr))]' => ! $hasProducts,
])>
    @foreach ($panels as $panel)
        @php
            $isProductPanel = ! empty($panel['products']);
            $columnClass = $isProductPanel
                ? 'lg:col-span-5'
                : ((($panel['columns'] ?? 1) >= 2) ? 'lg:col-span-4' : 'lg:col-span-3');
        @endphp

        <div @class([$columnClass, 'min-w-0'])>
            @if (! empty($panel['title']))
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-text-muted">
                    {{ $panel['title'] }}
                </p>
            @endif

            @if (! empty($panel['links']))
                <ul @class([
                    'mt-4 space-y-2.5 text-sm',
                    'columns-2 gap-x-6' => ($panel['columns'] ?? 1) >= 2,
                ])>
                    @foreach ($panel['links'] as $link)
                        <li class="break-inside-avoid">
                            <a
                                href="{{ $link['url'] }}"
                                class="text-text-DEFAULT transition-colors hover:text-text-muted"
                                @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                            >
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if (! empty($panel['products']))
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($panel['products'] as $product)
                        <a href="{{ $product['url'] }}" class="group/card flex min-w-0 flex-col">
                            <div class="relative aspect-[4/5] overflow-hidden rounded-lg bg-[#eceae6]">
                                <img
                                    src="{{ $product['image'] }}"
                                    alt="{{ $product['name'] }}"
                                    class="size-full object-cover object-center transition-transform duration-500 group-hover/card:scale-[1.03]"
                                    loading="lazy"
                                >
                                @if ($product['on_sale'] ?? false)
                                    <span class="absolute left-2 top-2 bg-sale-DEFAULT px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                        {{ __('shop.nav.promo_badge') }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-3 line-clamp-2 text-sm font-medium leading-snug text-primary-DEFAULT">
                                {{ $product['name'] }}
                            </p>
                            <div class="mt-1 flex flex-wrap items-baseline gap-2">
                                @if (! empty($product['compare_at_formatted']))
                                    <span class="text-xs text-text-muted line-through">{{ $product['compare_at_formatted'] }}</span>
                                @endif
                                @if (! empty($product['price_formatted']))
                                    <span @class([
                                        'text-sm font-semibold',
                                        'text-sale-DEFAULT' => $product['on_sale'] ?? false,
                                        'text-primary-DEFAULT' => ! ($product['on_sale'] ?? false),
                                    ])>{{ $product['price_formatted'] }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
