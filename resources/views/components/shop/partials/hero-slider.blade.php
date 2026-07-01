@props([
    'tiles',
    'positionMap',
    'textClass',
    'slideHeight' => 'h-[18rem] sm:h-[23rem]',
    'controlsClass' => 'mt-3 flex items-center justify-between gap-4 px-5 lg:px-0',
])

@if ($tiles->count() > 1)
    <div class="relative" data-hero-slider>
        <div class="overflow-hidden">
            <div class="flex w-full transition-transform duration-500 ease-out will-change-transform" data-hero-track>
                @foreach ($tiles as $item)
                    <div class="w-full min-w-full shrink-0 grow-0 basis-full">
                        <x-shop.hero-builder-tile
                            :item="$item"
                            :position-map="$positionMap"
                            :text-class="$textClass"
                            :class="$slideHeight"
                        />
                    </div>
                @endforeach
            </div>
        </div>

        <div class="{{ $controlsClass }}">
            <div class="flex items-center gap-2">
                @foreach ($tiles as $index => $unused)
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
@elseif ($tiles->count() === 1)
    <x-shop.hero-builder-tile
        :item="$tiles->first()"
        :position-map="$positionMap"
        :text-class="$textClass"
        :class="$slideHeight"
    />
@endif
