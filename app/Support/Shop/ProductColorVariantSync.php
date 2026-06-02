<?php

namespace App\Support\Shop;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductColorVariantSync
{
    public static function ensureAttributeValue(
        string $hex,
        ?string $label = null,
        ?string $slug = null,
        string $locale = 'pl',
    ): ?AttributeValue {
        $hex = trim($hex);
        if ($hex === '') {
            return null;
        }

        $colorAttr = Attribute::query()->where('code', 'color')->first();
        if (! $colorAttr) {
            return null;
        }

        $value = AttributeValue::query()
            ->where('attribute_id', $colorAttr->id)
            ->where('color_hex', $hex)
            ->first();

        if (! $value && $slug) {
            $value = AttributeValue::query()
                ->where('attribute_id', $colorAttr->id)
                ->where('code', $slug)
                ->first();
        }

        $code = $slug ?: ($label ? Str::slug($label) : 'color-'.ltrim($hex, '#'));

        $value = AttributeValue::query()->updateOrCreate(
            ['attribute_id' => $colorAttr->id, 'code' => $code],
            ['color_hex' => $hex, 'sort_order' => $value?->sort_order ?? 99]
        );

        if ($label) {
            $language = Language::query()->where('code', $locale)->first();
            if ($language) {
                $value->setTranslation('label', $label, $language);
            }
        }

        return $value;
    }

    public static function syncProduct(Product $product, string $locale = 'pl'): void
    {
        if (! $product->color_hex) {
            return;
        }

        $value = static::ensureAttributeValue(
            hex: $product->color_hex,
            label: $product->color_label,
            slug: $product->color_slug,
            locale: $locale,
        );

        if (! $value) {
            return;
        }

        /** @var ProductVariant|null $variant */
        $variant = $product->variants()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->first();

        if ($variant) {
            $variant->attributeValues()->syncWithoutDetaching([$value->id]);
        }
    }
}
