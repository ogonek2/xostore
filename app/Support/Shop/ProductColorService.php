<?php

namespace App\Support\Shop;

use App\Filament\Support\TranslationFormHelper;
use App\Models\Color;
use App\Models\Product;
use App\Support\Import\ImportUniqueCode;
use Illuminate\Support\Str;

final class ProductColorService
{
    public static function normalizeHex(?string $hex): ?string
    {
        if ($hex === null || trim($hex) === '') {
            return null;
        }

        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return null;
        }

        return '#'.Str::lower($hex);
    }

    public static function normalizeColorValue(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if ($hex = static::normalizeHex($value)) {
            return $hex;
        }

        if (preg_match('/^(hsl|hsla|rgb|rgba)\(/i', $value)) {
            return $value;
        }

        return null;
    }

    /**
     * Prefer catalog color over denormalized product / attribute fields.
     */
    public static function resolveHex(
        ?string $colorHex = null,
        ?int $colorId = null,
        ?string $colorCode = null,
    ): ?string {
        if ($colorId) {
            $hex = Color::query()->whereKey($colorId)->value('hex');

            if ($resolved = static::normalizeColorValue(is_string($hex) ? $hex : null)) {
                return $resolved;
            }
        }

        if (filled($colorCode)) {
            $hex = Color::query()
                ->whereRaw('LOWER(code) = ?', [Str::lower($colorCode)])
                ->value('hex');

            if ($resolved = static::normalizeColorValue(is_string($hex) ? $hex : null)) {
                return $resolved;
            }
        }

        return static::normalizeColorValue($colorHex);
    }

    public static function findByCodeOrName(string $input): ?Color
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        $byCode = Color::query()
            ->whereRaw('LOWER(code) = ?', [Str::lower($input)])
            ->first();

        if ($byCode) {
            return $byCode;
        }

        $needle = Str::lower($input);

        return Color::query()
            ->with('translates')
            ->whereHas('translates', function ($query) use ($needle): void {
                $query->where('field', 'name')
                    ->whereRaw('LOWER(value) = ?', [$needle]);
            })
            ->first();
    }

    public static function createFromPlName(string $namePl, ?string $hex = null): Color
    {
        $namePl = trim($namePl);

        $existing = static::findByCodeOrName($namePl);

        if ($existing) {
            $normalizedHex = static::normalizeHex($hex);

            if ($normalizedHex && $existing->hex !== $normalizedHex) {
                $existing->update(['hex' => $normalizedHex]);
            }

            return $existing;
        }

        $code = ImportUniqueCode::fromLabel(
            $namePl,
            fn (string $candidate): bool => Color::query()->where('code', $candidate)->exists(),
        );

        $color = Color::query()->create([
            'code' => $code,
            'hex' => static::normalizeHex($hex) ?? '#CCCCCC',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $defaultLocale = (string) config('shop.default_language', 'pl');
        $color->setTranslation('name', $namePl, $defaultLocale);

        TranslationFormHelper::save(
            $color,
            [$defaultLocale => ['name' => $namePl]],
            'color',
            ['name'],
        );

        return $color->fresh(['translates']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function resolveProductColorFields(array $data): array
    {
        if (! empty($data['create_new_color']) && filled($data['new_color_name_pl'] ?? null)) {
            $color = static::createFromPlName(
                (string) $data['new_color_name_pl'],
                $data['new_color_hex'] ?? null,
            );
            $data['color_id'] = $color->id;
        }

        unset($data['create_new_color'], $data['new_color_name_pl'], $data['new_color_hex']);

        if (! empty($data['color_id'])) {
            return static::applyColorToProductData($data, (int) $data['color_id']);
        }

        if (filled($data['color_label'] ?? null)) {
            $color = static::createFromPlName(
                (string) $data['color_label'],
                $data['color_hex'] ?? null,
            );
            $data['color_id'] = $color->id;

            return static::applyColorToProductData($data, $color->id);
        }

        $data['color_id'] = null;
        $data['color_label'] = null;
        $data['color_slug'] = null;
        $data['color_hex'] = null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyColorToProductData(array $data, int $colorId): array
    {
        $color = Color::query()->with('translates')->find($colorId);

        if (! $color) {
            return $data;
        }

        $defaultLocale = (string) config('shop.default_language', 'pl');

        $data['color_id'] = $color->id;
        $data['color_label'] = $color->translate('name', $defaultLocale) ?? $color->code;
        $data['color_slug'] = $color->code;
        $data['color_hex'] = static::normalizeColorValue($color->hex) ?? $color->hex;

        return $data;
    }

    public static function applyColorFromCatalog(Product $product): void
    {
        if (! $product->color_id) {
            return;
        }

        $product->loadMissing('color.translates');

        if (! $product->color) {
            return;
        }

        $defaultLocale = (string) config('shop.default_language', 'pl');
        $hex = static::normalizeColorValue($product->color->hex) ?? $product->color->hex;

        $product->forceFill([
            'color_label' => $product->color->translate('name', $defaultLocale) ?? $product->color->code,
            'color_slug' => $product->color->code,
            'color_hex' => $hex,
        ]);
    }

    public static function syncProduct(Product $product): void
    {
        static::applyColorFromCatalog($product);

        if (! $product->color_id || ! $product->isDirty(['color_label', 'color_slug', 'color_hex'])) {
            return;
        }

        $product->saveQuietly();
    }
}
