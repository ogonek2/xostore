@props([
    'label',
    'url' => null,
    'variant' => 'cta',
])

@if (filled($label))
    <a
        href="{{ filled($url) ? $url : '#' }}"
        @class([
            'landing-btn',
            match ($variant) {
                'inverse' => 'landing-btn--inverse',
                'outline' => 'landing-btn--outline',
                default => 'landing-btn--cta',
            },
        ])
    >
        {{ $label }}
    </a>
@endif
