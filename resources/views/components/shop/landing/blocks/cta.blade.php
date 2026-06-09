@php
    $settings = $block['settings'] ?? [];
    $style = $settings['style'] ?? 'primary';
    $align = $settings['align'] ?? 'center';
    $buttonVariant = match ($style) {
        'light' => 'cta',
        'outline' => 'outline',
        default => 'inverse',
    };
@endphp

<section @class([
    'landing-cta',
    'landing-cta--'.$style,
    'landing-cta--align-'.$align,
]) @if ($style === 'image' && ! empty($settings['image_url'])) style="--landing-cta-bg: url('{{ $settings['image_url'] }}')" @endif>
    <div class="landing-container landing-cta__inner">
        @if (filled($block['title'] ?? null))
            <h2 class="landing-cta__title">{{ $block['title'] }}</h2>
        @endif
        @if (filled($block['subtitle'] ?? null))
            <p class="landing-cta__subtitle">{{ $block['subtitle'] }}</p>
        @endif
        @if (filled($block['content_html'] ?? null))
            <div class="landing-cta__text landing-prose">{!! $block['content_html'] !!}</div>
        @endif
        <x-shop.landing.button
            :label="$block['button_label'] ?? null"
            :url="$block['link_url'] ?? null"
            :variant="$buttonVariant"
        />
    </div>
</section>
