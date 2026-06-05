<?php

namespace App\Support\Shop;

use App\Models\Language;
use App\Models\Product;
use App\Models\Translate;
use Illuminate\Support\Str;

final class ProductUniqueSlug
{
    /**
     * @param  array<string, string>  $importData
     */
    public static function forImport(
        Product $product,
        string $languageCode,
        string $name,
        array $importData = [],
    ): string {
        $languageId = Language::query()->where('code', $languageCode)->value('id');

        if (! $languageId) {
            return Str::slug($name);
        }

        $manualKey = $languageCode === 'pl' ? 'slug_pl' : ($languageCode === 'en' ? 'slug_en' : "slug_{$languageCode}");
        $base = filled($importData[$manualKey] ?? null)
            ? Str::slug($importData[$manualKey])
            : Str::slug($name);

        if ($base === '') {
            $base = Str::slug($product->sku);
        }

        return static::ensureUnique(
            $base,
            (int) $languageId,
            $product->id,
            $product->sku,
            $importData['color_slug'] ?? $product->color_slug,
            $importData['model_slug'] ?? $product->model_slug,
        );
    }

    public static function ensureUnique(
        string $base,
        int $languageId,
        ?int $excludeProductId = null,
        ?string $sku = null,
        ?string $colorSlug = null,
        ?string $modelSlug = null,
    ): string {
        $base = trim($base, '-');

        if ($base === '') {
            $base = 'product';
        }

        $candidates = collect([$base])
            ->when(filled($sku), fn ($c) => $c->push($base.'-'.Str::slug(Str::lower($sku))))
            ->when(filled($colorSlug), fn ($c) => $c->push($base.'-'.Str::slug($colorSlug)))
            ->when(filled($modelSlug) && filled($colorSlug), fn ($c) => $c->push(
                Str::slug($modelSlug).'-'.Str::slug($colorSlug),
            ))
            ->when(filled($sku), fn ($c) => $c->push(Str::slug(Str::lower($sku))))
            ->unique()
            ->values();

        foreach ($candidates as $candidate) {
            if (! static::isTaken($candidate, $languageId, $excludeProductId)) {
                return $candidate;
            }
        }

        $suffix = 2;

        while (static::isTaken("{$base}-{$suffix}", $languageId, $excludeProductId)) {
            $suffix++;

            if ($suffix > 500) {
                return $base.'-'.($excludeProductId ?? uniqid());
            }
        }

        return "{$base}-{$suffix}";
    }

    public static function isTaken(string $slug, int $languageId, ?int $excludeProductId = null): bool
    {
        $query = Translate::query()
            ->where('field', 'slug')
            ->where('language_id', $languageId)
            ->where('translatable_type', Product::class)
            ->where('value', $slug);

        if ($excludeProductId) {
            $query->where('translatable_id', '!=', $excludeProductId);
        }

        return $query->exists();
    }
}
