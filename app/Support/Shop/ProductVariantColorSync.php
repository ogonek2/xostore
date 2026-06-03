<?php

namespace App\Support\Shop;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

final class ProductVariantColorSync
{
    public static function syncProductVariants(Product $product): void
    {
        $value = static::resolveAttributeValue($product);

        if (! $value) {
            return;
        }

        $product->variants()->each(function (ProductVariant $variant) use ($value): void {
            $variant->attributeValues()->sync([$value->id]);
        });
    }

    public static function syncVariant(Product $product, ProductVariant $variant): void
    {
        $value = static::resolveAttributeValue($product);

        if ($value) {
            $variant->attributeValues()->sync([$value->id]);
        }
    }

    protected static function resolveAttributeValue(Product $product): ?AttributeValue
    {
        if (blank($product->color_hex) && blank($product->color_label)) {
            return null;
        }

        $attribute = Attribute::query()
            ->where('type', 'color_swatch')
            ->orderBy('sort_order')
            ->first();

        if (! $attribute) {
            return null;
        }

        $code = filled($product->color_slug)
            ? Str::slug($product->color_slug)
            : Str::slug((string) $product->color_label);

        if ($code === '') {
            $code = 'color-'.substr((string) $product->color_hex, 1, 6);
        }

        $value = AttributeValue::query()->firstOrCreate(
            [
                'attribute_id' => $attribute->id,
                'code' => $code,
            ],
            [
                'color_hex' => $product->color_hex,
                'sort_order' => 0,
            ],
        );

        if ($product->color_hex && $value->color_hex !== $product->color_hex) {
            $value->update(['color_hex' => $product->color_hex]);
        }

        $pl = config('shop.default_language', 'pl');
        $label = trim((string) $product->color_label);

        if ($label !== '') {
            $value->setTranslation('label', $label, $pl);
        }

        return $value;
    }
}
