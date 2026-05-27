<?php

namespace App\Filament\Support;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\SizeGridValue;
use Filament\Schemas\Components\Utilities\Get;

final class ProductAdminOptions
{
    /** @return array<int, string> */
    public static function colorAttributeValues(): array
    {
        return AttributeValue::query()
            ->whereHas('attribute', fn ($q) => $q->where('type', 'color_swatch'))
            ->with('translates')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (AttributeValue $value) => [
                $value->id => $value->translate('label', 'pl') ?? $value->code,
            ])
            ->all();
    }

    /** @return array<int, string> */
    public static function sizeGridValues(?int $sizeGridId): array
    {
        if (! $sizeGridId) {
            return [];
        }

        return SizeGridValue::query()
            ->where('size_grid_id', $sizeGridId)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (SizeGridValue $value) => [
                $value->id => $value->display_value ?? $value->value,
            ])
            ->all();
    }

    /** @return array<int, string> */
    public static function productPicker(?int $exceptId = null): array
    {
        return Product::query()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->orderBy('sku')
            ->get()
            ->mapWithKeys(fn (Product $product) => [
                $product->id => ($product->translate('name', 'pl') ?? $product->sku).' ('.$product->sku.')',
            ])
            ->all();
    }

    /** @return array<int, string> */
    public static function sizeGridValuesForForm(Get $get): array
    {
        return self::sizeGridValues($get('size_grid_id') ? (int) $get('size_grid_id') : null);
    }

    /** @return array<int, string> */
    public static function sizeGridValuesForRepeater(Get $get): array
    {
        $gridId = $get('../../size_grid_id');

        return self::sizeGridValues($gridId ? (int) $gridId : null);
    }
}
