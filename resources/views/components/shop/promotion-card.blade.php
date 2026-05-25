@props([
    'layout' => 'compact',
    'url' => '#',
    'title',
    'subtitle' => null,
    'ctaLabel' => null,
    'image' => null,
])

@php
    $isFeatured = $layout === 'featured';
@endphp

<a
    href="{{ $url }}"
    {{ $attributes->class([
        'group relative flex overflow-hidden rounded-2xl transition-shadow duration-300',
        'hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.35)]' => $isFeatured,
        'hover:shadow-[0_16px_40px_-12px_rgba(0,0,0,0.2)]' => ! $isFeatured,
        'min-h-[280px] flex-col sm:min-h-[320px] lg:min-h-0 lg:flex-row lg:min-h-[300px]' => $isFeatured,
        'min-h-[260px] flex-col bg-[#ebe8e3] sm:min-h-[280px] lg:min-h-[300px]' => ! $isFeatured,
    ]) }}
>
    @if ($isFeatured)
        <div class="flex flex-1 flex-col justify-center bg-surface-DEFAULT px-6 py-8 sm:px-8 lg:px-10 lg:py-10">
            <h3 class="text-xl font-semibold leading-tight tracking-tight text-primary-DEFAULT sm:text-2xl lg:text-[1.65rem]">
                {{ $title }}
            </h3>

            @if ($subtitle)
                <p class="mt-3 text-sm text-text-muted sm:text-[0.95rem]">
                    {{ $subtitle }}
                </p>
            @endif

            <span class="mt-6 inline-flex w-fit items-center justify-center bg-primary-DEFAULT px-7 py-3 text-sm font-medium text-text-inverse transition-colors group-hover:bg-primary-hover">
                {{ $ctaLabel ?? __('shop.promotions.cta') }}
            </span>
        </div>

        @if ($image)
            <div class="relative aspect-[4/3] flex-1 bg-[#1a1a1a] lg:aspect-auto">
                <img
                    src="{{ $image }}"
                    alt=""
                    class="size-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.02]"
                    loading="lazy"
                >
            </div>
        @endif
    @else
        <div class="flex flex-1 flex-col px-5 pt-6 sm:px-6 sm:pt-7">
            <h3 class="text-lg font-semibold leading-snug tracking-tight text-primary-DEFAULT sm:text-xl">
                {{ $title }}
            </h3>

            @if ($subtitle)
                <p class="mt-2 text-sm text-text-muted">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        @if ($image)
            <div class="relative mt-auto flex h-[55%] min-h-[140px] items-end justify-center overflow-hidden px-4 pb-0 pt-4 sm:px-6">
                <img
                    src="{{ $image }}"
                    alt=""
                    class="max-h-full w-auto max-w-[85%] object-contain object-bottom transition-transform duration-700 ease-out group-hover:scale-[1.03]"
                    loading="lazy"
                >
            </div>
        @endif
    @endif
</a>
