@php
    $settings = $block['settings'] ?? [];
    $level = $settings['level'] ?? 'h2';
    $style = $settings['style'] ?? 'default';
    $tag = in_array($level, ['h1', 'h2', 'h3'], true) ? $level : 'h2';
    $theme = ($settings['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
    $buttonVariant = $theme === 'dark' ? 'inverse' : 'cta';
@endphp

<x-shop.landing.section :block="$block" class="landing-heading landing-heading--{{ $style }}">
    <div class="landing-container">
        @if ($style === 'eyebrow' && filled($block['subtitle'] ?? null))
            <p class="landing-heading__eyebrow">{{ $block['subtitle'] }}</p>
        @endif
        @if (filled($block['title'] ?? null))
            <{{ $tag }} class="landing-heading__title">{{ $block['title'] }}</{{ $tag }}>
        @endif
        @if ($style !== 'eyebrow' && filled($block['subtitle'] ?? null))
            <p class="landing-heading__subtitle">{{ $block['subtitle'] }}</p>
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
