@php
    $settings = $block['settings'] ?? [];
    $imagePosition = ($settings['image_position'] ?? 'right') === 'left' ? 'left' : 'right';
    $theme = ($settings['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
    $buttonVariant = $theme === 'dark' ? 'inverse' : 'cta';
    $imageAspect = str_replace('/', '-', (string) ($settings['image_aspect'] ?? '4/5'));
@endphp

<x-shop.landing.section :block="$block">
    <div class="landing-container">
        <div @class([
            'landing-split',
            'landing-split--image-left' => $imagePosition === 'left',
            'landing-split--image-right' => $imagePosition === 'right',
        ])>
            <div class="landing-split__content">
                @if (filled($block['subtitle'] ?? null))
                    <p class="landing-split__eyebrow">{{ $block['subtitle'] }}</p>
                @endif
                @if (filled($block['title'] ?? null))
                    <h2 class="landing-split__title">{{ $block['title'] }}</h2>
                @endif
                @if (filled($block['content_html'] ?? null))
                    <div class="landing-prose landing-split__prose">{!! $block['content_html'] !!}</div>
                @endif
                @if (filled($block['button_label'] ?? null))
                    <div class="landing-block-actions landing-split__actions">
                        <x-shop.landing.button
                            :label="$block['button_label']"
                            :url="$block['link_url'] ?? null"
                            :variant="$buttonVariant"
                        />
                    </div>
                @endif
            </div>
            @if (! empty($settings['image_url']))
                <div class="landing-split__media">
                    <figure class="landing-split__figure">
                        <img
                            src="{{ $settings['image_url'] }}"
                            alt="{{ $block['caption'] ?? $block['title'] ?? '' }}"
                            class="landing-split__image landing-split__image--{{ $imageAspect }}"
                            loading="lazy"
                        >
                        @if (filled($block['caption'] ?? null))
                            <figcaption class="landing-split__caption">{{ $block['caption'] }}</figcaption>
                        @endif
                    </figure>
                </div>
            @endif
        </div>
    </div>
</x-shop.landing.section>
