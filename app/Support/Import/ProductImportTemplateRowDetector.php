<?php

namespace App\Support\Import;

use Illuminate\Support\Str;

final class ProductImportTemplateRowDetector
{
    /**
     * @param  array<string, string>  $data
     */
    public static function isMetaRow(array $data, int $offsetFromHeader): bool
    {
        if (Str::lower(trim($data['status'] ?? '')) === 'example') {
            return true;
        }

        if (static::matchesKnownLabels($data, 2)) {
            return true;
        }

        if (static::matchesKnownDescriptions($data, 2)) {
            return true;
        }

        if (static::looksLikePlaceholderSlugRow($data)) {
            return true;
        }

        if (static::looksLikeLabelHintRow($data)) {
            return true;
        }

        $namePl = trim($data['name_pl'] ?? '');

        return $offsetFromHeader === 2 && mb_strlen($namePl) > 60;
    }

    /**
     * @param  array<string, string>  $data
     */
    protected static function matchesKnownLabels(array $data, int $minMatches): bool
    {
        $known = static::knownLabels();
        $matches = 0;

        foreach ($data as $value) {
            $normalized = Str::lower(trim($value));

            if ($normalized === '') {
                continue;
            }

            if (in_array($normalized, $known, true)) {
                $matches++;

                if ($matches >= $minMatches) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, string>  $data
     */
    protected static function matchesKnownDescriptions(array $data, int $minMatches): bool
    {
        $known = static::knownDescriptionPrefixes();
        $matches = 0;

        foreach ($data as $value) {
            $normalized = Str::lower(trim($value));

            if (mb_strlen($normalized) < 40) {
                continue;
            }

            foreach ($known as $prefix) {
                if (str_starts_with($normalized, $prefix)) {
                    $matches++;

                    if ($matches >= $minMatches) {
                        return true;
                    }

                    break;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, string>  $data
     */
    protected static function looksLikePlaceholderSlugRow(array $data): bool
    {
        $slugPl = Str::lower(trim($data['slug_pl'] ?? ''));
        $slugEn = Str::lower(trim($data['slug_en'] ?? ''));
        $modelSlug = Str::lower(trim($data['model_slug'] ?? ''));

        if ($slugPl === 'pl' && $slugEn === 'en') {
            return true;
        }

        if ($modelSlug === 'slug' && in_array($slugPl, ['pl', 'en', 'slug'], true)) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, string>  $data
     */
    protected static function looksLikeLabelHintRow(array $data): bool
    {
        $namePl = trim($data['name_pl'] ?? '');
        $nameEn = trim($data['name_en'] ?? '');
        $sku = Str::lower(trim($data['sku'] ?? ''));
        $namePlLower = Str::lower($namePl);

        if ($namePl !== '' && preg_match('/^[\?\s\*_\-]+$/u', $namePl)) {
            return true;
        }

        $hintPatterns = [
            'название',
            'name_pl',
            'nazwa',
            'артикул',
            'artikul',
            'описание',
            'description',
            'бренд',
            'brand',
            'категор',
            'category',
            'код цвета',
            'color',
            'размер',
            'size',
            'цена',
            'price',
            'статус',
            'status',
            'тип товара',
            'url-адрес',
            'slug модели',
        ];

        foreach ($hintPatterns as $pattern) {
            if (str_contains($namePlLower, $pattern) || str_contains($sku, $pattern)) {
                if (
                    str_contains($namePl, '*')
                    || str_contains($sku, '*')
                    || str_contains($namePl, '(')
                    || str_contains($sku, '(')
                ) {
                    return true;
                }
            }
        }

        if (preg_match('/(?:^|\s)pl\s*\*?\s*$/i', $namePl) && mb_strlen($namePl) < 48) {
            return true;
        }

        if (preg_match('/(?:^|\s)en\s*$/i', $nameEn) && mb_strlen($nameEn) < 32) {
            return true;
        }

        if (
            str_contains($sku, 'артикул')
            || str_contains($sku, 'sku)')
            || str_contains($sku, '(sku')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected static function knownLabels(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = collect(ProductImportColumns::labelsRu())
            ->merge(ProductImportColumns::labelsPl())
            ->map(fn (string $label): string => Str::lower(trim($label)))
            ->filter(fn (string $label): bool => $label !== '')
            ->unique()
            ->values()
            ->all();

        return $cache;
    }

    /**
     * @return list<string>
     */
    protected static function knownDescriptionPrefixes(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = collect(ProductImportColumns::descriptions())
            ->map(fn (string $text): string => Str::lower(mb_substr(trim($text), 0, 80)))
            ->filter(fn (string $text): bool => mb_strlen($text) >= 40)
            ->unique()
            ->values()
            ->all();

        return $cache;
    }
}
