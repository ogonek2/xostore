@php
    $settings = $block['settings'] ?? [];
    $columns = (int) ($settings['columns'] ?? 3);
    $gap = $settings['gap'] ?? 'md';
    $items = $block['items'] ?? [];
@endphp

@if ($items !== [])
    <x-shop.landing.section :block="$block" data-landing-gallery>
        <div class="landing-container">
            @if (filled($block['title'] ?? null))
                <h2 class="landing-block-title">{{ $block['title'] }}</h2>
            @endif
            @if (filled($block['subtitle'] ?? null))
                <p class="landing-block-subtitle">{{ $block['subtitle'] }}</p>
            @endif
            <div class="landing-gallery landing-gallery--cols-{{ $columns }} landing-gallery--gap-{{ $gap }}">
                @foreach ($items as $index => $item)
                    <button
                        type="button"
                        class="landing-gallery__item"
                        data-landing-gallery-item
                        data-index="{{ $index }}"
                        data-full="{{ $item['image_url'] ?? '' }}"
                        aria-label="{{ $item['caption'] ?? $item['title'] ?? 'Image' }}"
                    >
                        @if (! empty($item['image_url']))
                            <img src="{{ $item['image_url'] }}" alt="{{ $item['caption'] ?? '' }}" loading="lazy">
                        @endif
                        @if (filled($item['caption'] ?? null))
                            <span class="landing-gallery__caption">{{ $item['caption'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
        <div class="landing-lightbox" data-landing-lightbox hidden>
            <button type="button" class="landing-lightbox__close" data-landing-lightbox-close aria-label="Close">&times;</button>
            <img src="" alt="" class="landing-lightbox__image" data-landing-lightbox-image>
        </div>
    </x-shop.landing.section>
@endif
