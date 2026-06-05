<?php

namespace App\Support\Import;

use Illuminate\Support\Str;

final class ProductImportModelSlugAnalyzer
{
    /**
     * @param  array<string, list<array{line: int, data: array<string, string>}>>  $groups
     * @return list<string>
     */
    public static function warnings(array $groups): array
    {
        /** @var array<string, list<string>> $skusByModelSlug */
        $skusByModelSlug = [];

        foreach ($groups as $sku => $entries) {
            $modelSlug = static::modelSlugFromEntries($entries);

            if ($modelSlug === null) {
                continue;
            }

            $skusByModelSlug[$modelSlug][] = (string) $sku;
        }

        $warnings = [];

        foreach ($skusByModelSlug as $modelSlug => $skus) {
            if (count($skus) < 2) {
                continue;
            }

            $preview = implode(', ', array_slice($skus, 0, 6));

            if (count($skus) > 6) {
                $preview .= '…';
            }

            $warnings[] = sprintf(
                'Общий model_slug «%s» у %d товаров (%s) — на сайте они будут показаны как цветовые варианты одной модели. Заполняйте model_slug только для одной модели в разных цветах (одинаковый slug + разные SKU и color_label). У остальных товаров оставьте колонку пустой.',
                $modelSlug,
                count($skus),
                $preview,
            );
        }

        return $warnings;
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     */
    public static function modelSlugFromEntries(array $entries): ?string
    {
        foreach ($entries as $entry) {
            $raw = trim((string) ($entry['data']['model_slug'] ?? ''));

            if ($raw !== '') {
                return Str::slug($raw);
            }
        }

        return null;
    }
}
