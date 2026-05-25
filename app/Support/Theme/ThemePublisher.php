<?php

namespace App\Support\Theme;

class ThemePublisher
{
    public function publish(): string
    {
        $css = $this->buildCss();
        $path = resource_path('css/theme-generated.css');
        file_put_contents($path, $css);

        return $path;
    }

    public function buildCss(): string
    {
        $lines = [
            '/* Auto-generated from config/theme — do not edit manually */',
            '/* Run: php artisan theme:publish */',
            '',
            '@theme {',
        ];

        foreach ($this->flattenColors(config('theme.colors')) as $name => $value) {
            $lines[] = "    --color-{$name}: {$value};";
        }

        foreach (config('theme.fonts.families') as $key => $family) {
            $stack = "'{$family['name']}', {$family['fallback']}";
            $lines[] = "    --font-{$key}: {$stack};";
        }

        foreach (config('theme.fonts.sizes') as $key => $size) {
            $lines[] = "    --text-{$key}: {$size['size']};";
            $lines[] = "    --text-{$key}--line-height: {$size['line']};";
        }

        $lines[] = '}';
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    protected function flattenColors(array $colors, string $prefix = ''): array
    {
        $flat = [];

        foreach ($colors as $key => $value) {
            $name = $prefix === '' ? (string) $key : "{$prefix}-{$key}";

            if (is_array($value)) {
                $flat = array_merge($flat, $this->flattenColors($value, $name));
            } else {
                $flat[$name] = $value;
            }
        }

        return $flat;
    }
}
