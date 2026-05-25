<?php

/**
 * Typography tokens for XOStore.
 * After changes run: php artisan theme:publish
 */
return [
    'families' => [
        'sans' => [
            'name' => 'Instrument Sans',
            'fallback' => 'ui-sans-serif, system-ui, sans-serif',
            'google' => null,
        ],
        'display' => [
            'name' => 'Cormorant Garamond',
            'fallback' => 'Georgia, serif',
            'google' => 'Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400',
        ],
        'mono' => [
            'name' => 'ui-monospace',
            'fallback' => 'SFMono-Regular, Menlo, Monaco, Consolas, monospace',
            'google' => null,
        ],
    ],
    'sizes' => [
        'xs' => ['size' => '0.75rem', 'line' => '1rem'],
        'sm' => ['size' => '0.875rem', 'line' => '1.25rem'],
        'base' => ['size' => '1rem', 'line' => '1.5rem'],
        'lg' => ['size' => '1.125rem', 'line' => '1.75rem'],
        'xl' => ['size' => '1.25rem', 'line' => '1.75rem'],
        '2xl' => ['size' => '1.5rem', 'line' => '2rem'],
        '3xl' => ['size' => '1.875rem', 'line' => '2.25rem'],
        '4xl' => ['size' => '2.25rem', 'line' => '2.5rem'],
        '5xl' => ['size' => '3rem', 'line' => '1.1'],
    ],
    'weights' => [
        'normal' => '400',
        'medium' => '500',
        'semibold' => '600',
        'bold' => '700',
    ],
];
