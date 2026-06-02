@props(['item', 'positionMap', 'textClass'])

@php
    $overlayOpacity = (int) ($item['overlay_opacity'] ?? 30);
    $overlay = max(0, min(90, $overlayOpacity)) / 100;
    $position = $positionMap[$item['text_position'] ?? 'bottom_left'] ?? $positionMap['bottom_left'];
@endphp

<article {{ $attributes->class(['relative min-h-0 overflow-hidden']) }}>
    <a href="{{ $item['link_url'] ?: ($item['button_url'] ?: '#') }}" class="relative block h-full">
        <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?: __('shop.hero.title') }}"
            class="absolute inset-0 h-full w-full object-cover object-center" loading="eager">
        <div class="absolute inset-0" style="background-color: rgba(0, 0, 0, {{ $overlay }});"></div>

        @if (!empty($item['title']) || !empty($item['subtitle']) || !empty($item['button_label']))
            <div class="absolute inset-0 flex {{ $position }}">
                <div class="w-full max-w-xl p-6 sm:p-8 lg:p-10 {{ $textClass($item) }}">
                    @if (!empty($item['title']))
                        <h2
                            class="mb-2 text-3xl font-bold uppercase leading-[1.05] tracking-tight sm:text-4xl lg:text-[2.4rem]">
                            {{ $item['title'] }}
                        </h2>
                    @endif
                    @if (!empty($item['subtitle']))
                        <p class="mb-5 text-sm font-medium tracking-wide opacity-90">
                            {{ $item['subtitle'] }}
                        </p>
                    @endif
                    @if (!empty($item['button_label']))
                        <span
                            class="inline-flex min-w-[9rem] items-center justify-center bg-white px-6 py-3 text-sm font-semibold uppercase tracking-[0.12em] text-primary-DEFAULT">
                            {{ $item['button_label'] }}
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </a>
</article>
