<?php

namespace App\Support\Shop;

use App\Models\Color;
use App\Models\Product;
use Illuminate\Support\Str;

final class ProductColorMigrator
{
    /**
     * @return array{created: int, linked: int, skipped: int, already_linked: int}
     */
    public static function migrate(bool $dryRun = false): array
    {
        $stats = [
            'created' => 0,
            'linked' => 0,
            'skipped' => 0,
            'already_linked' => 0,
        ];

        /** @var array<string, int> $resolvedByKey */
        $resolvedByKey = [];

        Product::query()
            ->where(function ($query): void {
                $query->whereNotNull('color_label')->where('color_label', '!=', '')
                    ->orWhere(function ($query): void {
                        $query->whereNotNull('color_slug')->where('color_slug', '!=', '');
                    });
            })
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($dryRun, &$stats, &$resolvedByKey): void {
                foreach ($products as $product) {
                    static::migrateProduct($product, $dryRun, $stats, $resolvedByKey);
                }
            });

        return $stats;
    }

    /**
     * @param  array{created: int, linked: int, skipped: int, already_linked: int}  $stats
     * @param  array<string, int>  $resolvedByKey
     */
    protected static function migrateProduct(
        Product $product,
        bool $dryRun,
        array &$stats,
        array &$resolvedByKey,
    ): void {
        if ($product->color_id) {
            $stats['already_linked']++;

            return;
        }

        $name = static::resolveColorName($product);

        if ($name === null) {
            $stats['skipped']++;

            return;
        }

        $cacheKey = static::dedupeKey($name, $product->color_slug);

        if (isset($resolvedByKey[$cacheKey])) {
            $colorId = $resolvedByKey[$cacheKey];

            if (! $dryRun) {
                static::linkProduct($product, $colorId);
            }

            $stats['linked']++;

            return;
        }

        $existing = static::findExistingColor($name, $product->color_slug);

        if ($existing) {
            $resolvedByKey[$cacheKey] = $existing->id;

            if (! $dryRun) {
                static::linkProduct($product, $existing->id);
            }

            $stats['linked']++;

            return;
        }

        if ($dryRun) {
            $resolvedByKey[$cacheKey] = 0;
            $stats['created']++;
            $stats['linked']++;

            return;
        }

        $beforeCount = Color::query()->count();
        $color = ProductColorService::createFromPlName($name, $product->color_hex);
        $resolvedByKey[$cacheKey] = $color->id;

        if (Color::query()->count() > $beforeCount) {
            $stats['created']++;
        }

        static::linkProduct($product, $color->id);
        $stats['linked']++;
    }

    protected static function resolveColorName(Product $product): ?string
    {
        $label = is_string($product->color_label) ? trim($product->color_label) : '';

        if ($label !== '') {
            return $label;
        }

        $slug = is_string($product->color_slug) ? trim($product->color_slug) : '';

        if ($slug === '') {
            return null;
        }

        return Str::title(str_replace('-', ' ', $slug));
    }

    protected static function dedupeKey(string $name, ?string $slug): string
    {
        $slugKey = is_string($slug) ? Str::lower(trim($slug)) : '';

        return Str::lower(trim($name)).'|'.$slugKey;
    }

    protected static function findExistingColor(string $name, ?string $slug): ?Color
    {
        if (is_string($slug) && trim($slug) !== '') {
            $bySlug = ProductColorService::findByCodeOrName(trim($slug));

            if ($bySlug) {
                return $bySlug;
            }
        }

        return ProductColorService::findByCodeOrName($name);
    }

    protected static function linkProduct(Product $product, int $colorId): void
    {
        $data = ProductColorService::applyColorToProductData(
            ['color_id' => $colorId],
            $colorId,
        );

        $product->forceFill([
            'color_id' => $data['color_id'],
            'color_label' => $data['color_label'],
            'color_slug' => $data['color_slug'],
            'color_hex' => $data['color_hex'],
        ])->saveQuietly();
    }
}
