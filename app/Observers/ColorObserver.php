<?php

namespace App\Observers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Color;
use App\Models\Product;
use App\Support\Shop\ProductColorService;
use App\Support\Shop\ProductColorVariantSync;

class ColorObserver
{
    public function saved(Color $color): void
    {
        if (! $color->wasChanged('hex')) {
            return;
        }

        $hex = ProductColorService::normalizeColorValue($color->hex) ?? $color->hex;

        Product::query()
            ->where('color_id', $color->id)
            ->update(['color_hex' => $hex]);

        $colorAttr = Attribute::query()->where('code', 'color')->first();

        if ($colorAttr) {
            AttributeValue::query()
                ->where('attribute_id', $colorAttr->id)
                ->where('code', $color->code)
                ->update(['color_hex' => $hex]);
        }

        Product::query()
            ->where('color_id', $color->id)
            ->with('variants')
            ->chunkById(50, function ($products): void {
                foreach ($products as $product) {
                    ProductColorVariantSync::syncProduct($product, config('shop.default_language', 'pl'));
                }
            });
    }
}
