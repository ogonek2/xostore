@props([
    'featured' => null,
    'compact',
    'viewAllUrl' => '#',
    'viewMoreUrl' => '#',
    'title' => null,
    'viewAllLabel' => null,
    'viewMoreLabel' => null,
])

@php
    $hasCards = $featured || $compact->isNotEmpty();
@endphp

<section class="bg-primary-DEFAULT py-14 text-text-inverse lg:py-20">
    <div class="mx-auto max-w-[90rem] px-5 lg:px-8">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-4 lg:mb-12">
            <h2 class="text-2xl font-semibold tracking-tight lg:text-[1.75rem]">
                {{ $title ?? __('shop.promotions.title') }}
            </h2>

            <a
                href="{{ $viewAllUrl }}"
                class="text-xs font-medium uppercase tracking-[0.2em] text-text-inverse/80 underline decoration-text-inverse/40 underline-offset-[6px] transition-colors hover:text-text-inverse hover:decoration-text-inverse"
            >
                {{ $viewAllLabel ?? __('shop.promotions.view_all') }}
            </a>
        </div>

        @if ($hasCards)
            <div class="grid gap-4 sm:gap-5 lg:grid-cols-4 lg:gap-6">
                @if ($featured)
                    <div class="lg:col-span-2">
                        <x-shop.promotion-card
                            layout="featured"
                            :url="$featured['url']"
                            :title="$featured['title']"
                            :subtitle="$featured['subtitle']"
                            :cta-label="$featured['cta_label']"
                            :image="$featured['image']"
                            class="h-full"
                        />
                    </div>
                @endif

                @foreach ($compact as $card)
                    <div @class(['lg:col-span-1' => $featured, 'lg:col-span-2' => ! $featured])>
                        <x-shop.promotion-card
                            layout="compact"
                            :url="$card['url']"
                            :title="$card['title']"
                            :subtitle="$card['subtitle']"
                            :image="$card['image']"
                            class="h-full"
                        />
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-10 flex justify-end lg:mt-12">
            <a
                href="{{ $viewMoreUrl }}"
                class="inline-flex min-w-[10rem] items-center justify-center bg-surface-DEFAULT px-8 py-3.5 text-sm font-medium text-primary-DEFAULT transition-colors hover:bg-surface-muted"
            >
                {{ $viewMoreLabel ?? __('shop.promotions.view_more') }}
            </a>
        </div>
    </div>
</section>
