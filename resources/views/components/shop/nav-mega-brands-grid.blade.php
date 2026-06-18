@props([
    'brands' => [],
    'compact' => false,
])

<div @class([
    'mega-brands-grid grid gap-2.5 sm:gap-3',
    'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6' => ! $compact,
    'grid-cols-2' => $compact,
])>
    @foreach ($brands as $brand)
        <a
            href="{{ $brand['url'] }}"
            class="mega-brand-tile group/tile relative flex min-h-[4.5rem] flex-col items-center justify-center overflow-hidden rounded-sm border border-border-DEFAULT/35 bg-gradient-to-b from-[#faf9f7] to-[#f3f1ed] px-3 py-4 text-center transition-all duration-300 hover:-translate-y-0.5 hover:border-primary-DEFAULT/25 hover:from-white hover:to-[#faf9f7] hover:shadow-[0_10px_30px_-12px_rgba(0,0,0,0.18)] sm:min-h-[5rem]"
        >
            <span
                class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/90 to-transparent"
                aria-hidden="true"
            ></span>

            @if (! empty($brand['logo']))
                <img
                    src="{{ $brand['logo'] }}"
                    alt="{{ $brand['name'] }}"
                    class="max-h-7 max-w-[85%] object-contain opacity-75 grayscale transition-all duration-300 group-hover/tile:opacity-100 group-hover/tile:grayscale-0 sm:max-h-8"
                    loading="lazy"
                >
            @else
                <span class="px-1 text-[10px] font-medium uppercase leading-tight tracking-[0.2em] text-primary-DEFAULT sm:text-[11px]">
                    {{ $brand['name'] }}
                </span>
            @endif

            <span class="mt-2 text-[9px] font-medium uppercase tracking-[0.16em] text-text-muted opacity-0 transition-opacity duration-300 group-hover/tile:opacity-100">
                {{ __('shop.nav.explore_brand') }}
            </span>
        </a>
    @endforeach
</div>
