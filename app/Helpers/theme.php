<?php

use App\Services\Locale\CurrentLanguage;

if (! function_exists('current_language')) {
    function current_language(): \App\Models\Language
    {
        return app(CurrentLanguage::class)->get();
    }
}

if (! function_exists('theme_color')) {
    function theme_color(string $key, string $shade = 'DEFAULT'): ?string
    {
        $colors = config('theme.colors');
        $parts = explode('.', $key);
        $node = $colors;

        foreach ($parts as $part) {
            if (! is_array($node) || ! array_key_exists($part, $node)) {
                return null;
            }
            $node = $node[$part];
        }

        if (is_array($node)) {
            return $node[$shade] ?? $node['DEFAULT'] ?? reset($node);
        }

        return is_string($node) ? $node : null;
    }
}

if (! function_exists('theme_font')) {
    function theme_font(string $family = 'sans'): ?string
    {
        return config("theme.fonts.families.{$family}.name");
    }
}
