@props([
    'block' => [],
    'padding' => null,
])

@php
    $settings = $block['settings'] ?? [];
    $themeValue = $settings['theme'] ?? 'light';
    $alignValue = $settings['align'] ?? 'center';
    $theme = in_array($themeValue, ['dark', 'light'], true) ? $themeValue : 'light';
    $align = in_array($alignValue, ['left', 'center', 'right'], true) ? $alignValue : 'center';
    $pad = $padding ?? ($settings['padding'] ?? null);
@endphp

<section
    {{ $attributes->class(array_filter([
        'landing-section',
        "landing-section--theme-{$theme}",
        "landing-section--align-{$align}",
        $pad ? "landing-section--pad-{$pad}" : null,
    ])) }}
>
    {{ $slot }}
</section>
