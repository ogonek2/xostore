@php
    $settings = $block['settings'] ?? [];
    $width = $settings['width'] ?? 'default';
    $padding = $settings['padding'] ?? 'md';
    $theme = ($settings['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
    $buttonVariant = $theme === 'dark' ? 'inverse' : 'cta';
@endphp

<x-shop.landing.section :block="$block" :padding="$padding">
    <div class="landing-container landing-container--{{ $width }}">
        @if (filled($block['title'] ?? null))
            <h2 class="landing-block-title">{{ $block['title'] }}</h2>
        @endif
        @if (filled($block['subtitle'] ?? null))
            <p class="landing-block-subtitle">{{ $block['subtitle'] }}</p>
        @endif
        @if (filled($block['content_html'] ?? null))
            <div class="landing-prose">{!! $block['content_html'] !!}</div>
        @endif
        @if (filled($block['button_label'] ?? null))
            <div class="landing-block-actions">
                <x-shop.landing.button
                    :label="$block['button_label']"
                    :url="$block['link_url'] ?? null"
                    :variant="$buttonVariant"
                />
            </div>
        @endif
    </div>
</x-shop.landing.section>
