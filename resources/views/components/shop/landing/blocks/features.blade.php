@php
    $settings = $block['settings'] ?? [];
    $columns = (int) ($settings['columns'] ?? 3);
    $items = $block['items'] ?? [];
@endphp

@if ($items !== [])
    <x-shop.landing.section :block="$block">
        <div class="landing-container">
            @if (filled($block['title'] ?? null))
                <h2 class="landing-block-title">{{ $block['title'] }}</h2>
            @endif
            @if (filled($block['subtitle'] ?? null))
                <p class="landing-block-subtitle">{{ $block['subtitle'] }}</p>
            @endif
            <div class="landing-features landing-features--cols-{{ $columns }}">
                @foreach ($items as $item)
                    <article class="landing-features__card">
                        @if (! empty($item['image_url']))
                            <img src="{{ $item['image_url'] }}" alt="" class="landing-features__image" loading="lazy">
                        @elseif (filled($item['icon'] ?? null))
                            <span class="landing-features__icon">{{ $item['icon'] }}</span>
                        @endif
                        @if (filled($item['title'] ?? null))
                            <h3 class="landing-features__title">{{ $item['title'] }}</h3>
                        @endif
                        @if (filled($item['subtitle'] ?? null))
                            <p class="landing-features__text">{{ $item['subtitle'] }}</p>
                        @endif
                        @if (filled($item['content_html'] ?? null))
                            <div class="landing-prose landing-features__text">{!! $item['content_html'] !!}</div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </x-shop.landing.section>
@endif
