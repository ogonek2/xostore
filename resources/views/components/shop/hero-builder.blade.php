@props([
    'sections' => [],
])

@php
    $slides = collect($sections)
        ->filter(fn ($section) => collect($section['items'] ?? [])->contains(fn ($item) => ! empty($item['image'])))
        ->values();
    $hasMultiple = $slides->count() > 1;

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

@if ($slides->isNotEmpty())
    <section class="mx-auto max-w-[90rem]">
        @if ($hasMultiple)
            <div class="relative" data-hero-slider>
                <div class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-out" data-hero-track>
                    @foreach ($slides as $section)
                        <div class="w-full shrink-0">
                            <x-shop.hero-builder-layout
                                :section="$section"
                                :position-map="$positionMap"
                                :text-class="$textClass"
                            />
                        </div>
                    @endforeach
                </div>
                </div>

                <div class="mt-3 flex items-center justify-between gap-4 px-1">
                    <div class="flex items-center gap-2">
                        @foreach ($slides as $index => $unused)
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
            <x-shop.hero-builder-layout
                :section="$slides->first()"
                :position-map="$positionMap"
                :text-class="$textClass"
            />
        @endif
    </section>
@else
    <x-shop.hero />
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-hero-slider]').forEach((slider) => {
                    const track = slider.querySelector('[data-hero-track]');
                    const prev = slider.querySelector('[data-hero-prev]');
                    const next = slider.querySelector('[data-hero-next]');
                    const dots = [...slider.querySelectorAll('[data-hero-dot]')];
                    const total = dots.length;
                    let current = 0;

                    const update = () => {
                        track.style.transform = `translateX(-${current * 100}%)`;
                        dots.forEach((dot, index) => {
                            dot.dataset.active = index === current ? 'true' : 'false';
                        });
                    };

                    prev?.addEventListener('click', () => {
                        current = (current - 1 + total) % total;
                        update();
                    });

                    next?.addEventListener('click', () => {
                        current = (current + 1) % total;
                        update();
                    });

                    dots.forEach((dot) => {
                        dot.addEventListener('click', () => {
                            current = Number(dot.dataset.index || 0);
                            update();
                        });
                    });
                });
            });
        </script>
    @endpush
@endonce
