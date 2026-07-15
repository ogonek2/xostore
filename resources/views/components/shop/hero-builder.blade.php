@props([
    'sections' => [],
])

@php
    $sections = collect($sections)
        ->filter(fn ($section) => collect($section['items'] ?? [])->contains(fn ($item) => ! empty($item['image'])))
        ->values();

    $sectionTiles = function (array $section) {
        $layout = $section['layout'] ?? 'single';
        $items = collect($section['items'] ?? [])->filter(fn ($item) => ! empty($item['image']))->values();

        return match ($layout) {
            'two_columns' => $items->take(2),
            'three_columns' => $items->take(3),
            'feature_stack' => $items->take(3),
            default => $items->take(1),
        };
    };

    $mobileTiles = $sections->flatMap($sectionTiles)->values();
    $hasMultipleDesktopSections = $sections->count() > 1;
    $slideHeight = 'h-[min(72vw,22rem)] sm:h-[min(56vw,26rem)] lg:h-[min(42vw,32rem)]';

    $positionMap = [
        'top_left' => 'items-start justify-start text-left',
        'top_center' => 'items-start justify-center text-center',
        'top_right' => 'items-start justify-end text-right',
        'center_left' => 'items-center justify-start text-left',
        'center' => 'items-center justify-center text-center',
        'center_right' => 'items-center justify-end text-right',
        'bottom_left' => 'items-end justify-start text-left',
        'bottom_center' => 'items-end justify-center text-center',
        'bottom_right' => 'items-end justify-end text-right',
    ];

    $textClass = fn ($item) => ($item['text_color'] ?? 'light') === 'dark'
        ? 'text-primary-DEFAULT'
        : 'text-white';
@endphp

@if ($sections->isNotEmpty())
    <section class="mx-auto max-w-[90rem] px-0 pb-2 lg:px-8 lg:pb-4">
        <div class="lg:hidden">
            @include('components.shop.partials.hero-slider', [
                'tiles' => $mobileTiles,
                'positionMap' => $positionMap,
                'textClass' => $textClass,
                'slideHeight' => $slideHeight,
                'imageFit' => 'contain',
            ])
        </div>

        <div class="hidden lg:block">
            @if ($hasMultipleDesktopSections)
                <div class="relative" data-hero-slider>
                    <div class="overflow-hidden">
                        <div class="flex w-full transition-transform duration-500 ease-out will-change-transform" data-hero-track>
                            @foreach ($sections as $section)
                                <div class="w-full min-w-full shrink-0 grow-0 basis-full">
                                    <x-shop.hero-builder-layout
                                        :section="$section"
                                        :position-map="$positionMap"
                                        :text-class="$textClass"
                                    />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-3 flex min-h-8 items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            @foreach ($sections as $index => $unused)
                                <button
                                    type="button"
                                    class="h-2 w-2 rounded-full bg-black/35 transition data-[active=true]:w-5 data-[active=true]:bg-black"
                                    data-hero-dot
                                    data-index="{{ $index }}"
                                    data-active="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-label="Slide {{ $index + 1 }}"
                                ></button>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="inline-flex h-8 w-10 items-center justify-center rounded-md bg-black text-white transition hover:bg-black/85"
                                data-hero-prev
                                aria-label="Previous slide"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="inline-flex h-8 w-10 items-center justify-center rounded-md bg-black text-white transition hover:bg-black/85"
                                data-hero-next
                                aria-label="Next slide"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div>
                    <x-shop.hero-builder-layout
                        :section="$sections->first()"
                        :position-map="$positionMap"
                        :text-class="$textClass"
                    />
                    {{-- Same footprint as slider controls when pagination is hidden --}}
                    <div class="mt-3 min-h-8" aria-hidden="true"></div>
                </div>
            @endif
        </div>
    </section>
@else
    <x-shop.hero />
@endif
