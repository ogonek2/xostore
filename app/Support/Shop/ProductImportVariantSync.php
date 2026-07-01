<?php

namespace App\Support\Shop;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeGrid;
use App\Models\SizeGridValue;
use App\Support\Import\ProductExcelVariantParser;
use Illuminate\Support\Str;

final class ProductImportVariantSync
{
    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     * @param  array{variants_created: int, variants_updated: int, warnings: list<string>}  $result
     */
    public static function syncAfterImport(Product $product, array $entries, array &$result): void
    {
        $product->refresh();
        $product->loadMissing(['sizeGrid.values', 'variants.sizeGridValue']);

        if (! $product->size_grid_id) {
            return;
        }

        $definitions = static::collectDefinitions($product, $entries);

        static::removePlaceholderDefaultVariant($product);

        $grid = $product->sizeGrid;

        if (! $grid || $grid->values->isEmpty()) {
            if ($definitions === []) {
                $result['warnings'][] = "Товар {$product->sku}: пресет размеров «{$grid?->code}» без значений — кнопки размеров на сайте не появятся.";
            }

            return;
        }

        $hasSizedVariants = $product->variants()
            ->where('is_active', true)
            ->whereNotNull('size_grid_value_id')
            ->exists();

        if ($hasSizedVariants) {
            static::attachMissingSizeGridValues($product, $definitions, $result);

            return;
        }

        if ($definitions === []) {
            return;
        }

        $basePrice = (float) ($product->base_price ?? 0);
        $compareAt = $product->compare_at_price ? (float) $product->compare_at_price : null;

        foreach ($grid->values as $index => $gridValue) {
            $sizeCode = (string) ($gridValue->value ?: $gridValue->display_value);
            $variantSku = Str::upper("{$product->sku}-".Str::upper($sizeCode));

            $variant = ProductVariant::query()->firstOrNew([
                'product_id' => $product->id,
                'sku' => $variantSku,
            ]);

            $isNew = ! $variant->exists;

            $variant->fill([
                'price' => $basePrice,
                'compare_at_price' => $compareAt,
                'stock_qty' => 0,
                'size_grid_value_id' => $gridValue->id,
                'is_default' => $index === 0,
                'is_active' => true,
            ]);
            $variant->save();

            if ($isNew) {
                $result['variants_created']++;
            } else {
                $result['variants_updated']++;
            }
        }

        $result['warnings'][] = "Товар {$product->sku}: размеры созданы автоматически из пресета «{$grid->code}» (укажите variant_sizes в файле, чтобы задать остатки и цены по размерам).";
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     * @return list<array{size: ?string, sku: ?string, price: ?float, compare_at_price: ?float, stock: ?int, barcode: ?string, is_default: ?bool}>
     */
    protected static function collectDefinitions(Product $product, array $entries): array
    {
        $definitions = [];
        $basePrice = $product->base_price ? (float) $product->base_price : null;

        foreach ($entries as $entry) {
            if (! ProductExcelVariantParser::hasVariantInput($entry['data'])) {
                continue;
            }

            $definitions = [
                ...$definitions,
                ...ProductExcelVariantParser::definitions($entry['data'], $product->sku, $basePrice),
            ];
        }

        return $definitions;
    }

    protected static function removePlaceholderDefaultVariant(Product $product): void
    {
        $placeholderSku = Str::upper("{$product->sku}-DEFAULT");

        $product->variants()
            ->where('sku', $placeholderSku)
            ->whereNull('size_grid_value_id')
            ->delete();
    }

    /**
     * @param  list<array{size: ?string, sku: ?string, price: ?float, compare_at_price: ?float, stock: ?int, barcode: ?string, is_default: ?bool}>  $definitions
     * @param  array{variants_created: int, variants_updated: int, warnings: list<string>}  $result
     */
    protected static function attachMissingSizeGridValues(Product $product, array $definitions, array &$result): void
    {
        if ($definitions === [] || ! $product->size_grid_id) {
            return;
        }

        foreach ($product->variants()->whereNull('size_grid_value_id')->get() as $variant) {
            $definition = collect($definitions)->first(
                fn (array $row): bool => filled($row['sku'] ?? null)
                    && Str::upper((string) $row['sku']) === Str::upper($variant->sku),
            );

            $sizeCode = $definition['size'] ?? null;

            if (blank($sizeCode)) {
                continue;
            }

            $gridValue = static::findSizeGridValue($product->size_grid_id, (string) $sizeCode);

            if (! $gridValue) {
                continue;
            }

            $variant->update(['size_grid_value_id' => $gridValue->id]);
            $result['variants_updated']++;
        }
    }

    protected static function findSizeGridValue(int $sizeGridId, string $sizeCode): ?SizeGridValue
    {
        $needle = Str::lower(trim($sizeCode));

        return SizeGridValue::query()
            ->where('size_grid_id', $sizeGridId)
            ->where(function ($query) use ($needle, $sizeCode): void {
                $query->whereRaw('LOWER(value) = ?', [$needle])
                    ->orWhereRaw('LOWER(display_value) = ?', [$needle])
                    ->orWhereRaw('LOWER(display_value) = ?', [Str::lower($sizeCode)]);
            })
            ->first();
    }

    public static function ensureSizeGridHasValues(SizeGrid $grid, string $sourceCode): void
    {
        if ($grid->values()->exists()) {
            return;
        }

        $templateValues = \App\Support\Import\ImportSizePresetCatalog::templateValuesForCode($sourceCode);

        if ($templateValues === []) {
            return;
        }

        foreach ($templateValues as $index => $row) {
            SizeGridValue::query()->updateOrCreate(
                [
                    'size_grid_id' => $grid->id,
                    'value' => $row['value'],
                ],
                [
                    'display_value' => $row['display_value'],
                    'sort_order' => $row['sort_order'] ?? $index,
                ],
            );
        }
    }
}
