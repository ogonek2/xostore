@php
    $settings = $block['settings'] ?? [];
    $aspect = str_replace('/', '-', (string) ($settings['aspect'] ?? '16-9'));
    $width = $settings['width'] ?? 'contained';
    $video = $settings['video_url'] ?? null;
@endphp

<x-shop.landing.section :block="$block">
    <div @class([
        'landing-media',
        'landing-media--full' => $width === 'full',
        'landing-container' => $width !== 'full',
    ])>
        <figure class="landing-media__frame landing-media__frame--{{ $aspect }}">
            @if ($video)
                <div class="landing-media__video">
                    <iframe src="{{ $video }}" title="{{ $block['caption'] ?? 'Video' }}" loading="lazy" allowfullscreen></iframe>
                </div>
            @elseif (! empty($settings['image_url']))
                @if (filled($block['link_url'] ?? null))
                    <a href="{{ $block['link_url'] }}">
                        <img src="{{ $settings['image_url'] }}" alt="{{ $block['caption'] ?? $block['title'] ?? '' }}" loading="lazy" class="landing-media__image">
                    </a>
                @else
                    <img src="{{ $settings['image_url'] }}" alt="{{ $block['caption'] ?? $block['title'] ?? '' }}" loading="lazy" class="landing-media__image">
                @endif
            @endif
            @if (filled($block['caption'] ?? null))
                <figcaption class="landing-media__caption">{{ $block['caption'] }}</figcaption>
            @endif
        </figure>
    </div>
</x-shop.landing.section>
