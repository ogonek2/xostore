@php
    $settings = $block['settings'] ?? [];
    $height = $settings['height'] ?? 'lg';
    $align = $settings['text_align'] ?? 'center';
    $textColor = $settings['text_color'] ?? 'light';
    $overlay = (int) ($settings['overlay_opacity'] ?? 35);
@endphp

<section
    class="landing-hero landing-hero--{{ $height }} landing-hero--align-{{ $align }} landing-hero--text-{{ $textColor }}"
    @if (! empty($settings['image_url']))
        style="--landing-hero-image: url('{{ $settings['image_url'] }}'); --landing-hero-overlay: {{ $overlay / 100 }};"
    @endif
>
    <div class="landing-hero__overlay"></div>
    <div class="landing-container landing-hero__inner">
        @if (filled($block['subtitle'] ?? null))
            <p class="landing-hero__eyebrow">{{ $block['subtitle'] }}</p>
        @endif
        @if (filled($block['title'] ?? null))
            <h1 class="landing-hero__title">{{ $block['title'] }}</h1>
        @endif
        @if (filled($block['content_html'] ?? null))
            <div class="landing-hero__text landing-prose">{!! $block['content_html'] !!}</div>
        @endif
        <x-shop.landing.button
            :label="$block['button_label'] ?? null"
            :url="$block['link_url'] ?? null"
            :variant="($textColor === 'dark') ? 'cta' : 'inverse'"
        />
    </div>
</section>
