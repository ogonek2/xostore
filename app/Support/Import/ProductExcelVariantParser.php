<?php

namespace App\Support\Import;

use Illuminate\Support\Str;

final class ProductExcelVariantParser
{
    /**
     * @return list<array{
     *     size: ?string,
     *     sku: ?string,
     *     price: ?float,
     *     compare_at_price: ?float,
     *     stock: ?int,
     *     barcode: ?string,
     *     is_default: ?bool
     * }>
     */
    public static function definitions(array $data, string $productSku, ?float $basePrice = null): array
    {
        if (filled($data['variants'] ?? null)) {
            return self::fromCompactString($data['variants'], $productSku, $basePrice);
        }

        $sizes = self::sizesFromData($data);

        if ($sizes !== []) {
            return self::fromParallelLists($data, $sizes, $productSku, $basePrice);
        }

        if (self::hasSingleVariantFields($data)) {
            return [self::singleDefinition($data, $productSku, $basePrice)];
        }

        return [];
    }

    public static function hasVariantInput(array $data): bool
    {
        return filled($data['variants'] ?? null)
            || filled($data['variant_sizes'] ?? null)
            || filled($data['variant_size'] ?? null)
            || filled($data['variant_sku'] ?? null)
            || filled($data['variant_skus'] ?? null)
            || filled($data['variant_price'] ?? null)
            || filled($data['variant_prices'] ?? null)
            || filled($data['variant_stock'] ?? null)
            || filled($data['variant_stocks'] ?? null);
    }

    /**
     * @return list<string>
     */
    protected static function sizesFromData(array $data): array
    {
        if (filled($data['variant_sizes'] ?? null)) {
            return ProductExcelRowParser::parseList($data['variant_sizes']);
        }

        if (filled($data['variant_size'] ?? null)) {
            $raw = $data['variant_size'];

            if (str_contains($raw, ',')) {
                return ProductExcelRowParser::parseList($raw);
            }
        }

        return [];
    }

    protected static function hasSingleVariantFields(array $data): bool
    {
        return filled($data['variant_size'] ?? null)
            || filled($data['variant_sku'] ?? null)
            || filled($data['variant_price'] ?? null)
            || filled($data['variant_stock'] ?? null)
            || filled($data['variant_barcode'] ?? null);
    }

    /**
     * @return list<array{size: ?string, sku: ?string, price: ?float, compare_at_price: ?float, stock: ?int, barcode: ?string, is_default: ?bool}>
     */
    protected static function fromCompactString(string $value, string $productSku, ?float $basePrice): array
    {
        $definitions = [];

        foreach (ProductExcelRowParser::parseList($value) as $chunk) {
            $parts = array_map('trim', explode(':', $chunk));
            $size = $parts[0] ?? null;

            if (blank($size)) {
                continue;
            }

            $definitions[] = [
                'size' => $size,
                'sku' => filled($parts[3] ?? null) ? Str::upper($parts[3]) : null,
                'price' => ProductExcelRowParser::parseFloat($parts[1] ?? null) ?? $basePrice,
                'compare_at_price' => null,
                'stock' => ProductExcelRowParser::parseInt($parts[2] ?? null),
                'barcode' => null,
                'is_default' => null,
            ];
        }

        return $definitions;
    }

    /**
     * @param  list<string>  $sizes
     * @return list<array{size: ?string, sku: ?string, price: ?float, compare_at_price: ?float, stock: ?int, barcode: ?string, is_default: ?bool}>
     */
    protected static function fromParallelLists(array $data, array $sizes, string $productSku, ?float $basePrice): array
    {
        $prices = ProductExcelRowParser::parseNumericList($data['variant_prices'] ?? $data['variant_price'] ?? null);
        $comparePrices = ProductExcelRowParser::parseNumericList($data['variant_compare_at_prices'] ?? $data['variant_compare_at_price'] ?? null);
        $stocks = ProductExcelRowParser::parseIntList($data['variant_stocks'] ?? $data['variant_stock'] ?? null);
        $skus = ProductExcelRowParser::parseList($data['variant_skus'] ?? $data['variant_sku'] ?? null);
        $barcodes = ProductExcelRowParser::parseList($data['variant_barcodes'] ?? $data['variant_barcode'] ?? null);
        $defaults = ProductExcelRowParser::parseList($data['variant_defaults'] ?? $data['variant_is_default'] ?? null);

        $definitions = [];

        foreach ($sizes as $index => $size) {
            $definitions[] = [
                'size' => $size,
                'sku' => isset($skus[$index]) ? Str::upper($skus[$index]) : null,
                'price' => isset($prices[$index]) ? (float) $prices[$index] : ($prices[0] ?? $basePrice),
                'compare_at_price' => isset($comparePrices[$index]) ? (float) $comparePrices[$index] : ($comparePrices[0] ?? null),
                'stock' => isset($stocks[$index]) ? (int) $stocks[$index] : ($stocks[0] ?? 0),
                'barcode' => $barcodes[$index] ?? $barcodes[0] ?? null,
                'is_default' => isset($defaults[$index])
                    ? ProductExcelRowParser::parseBool($defaults[$index])
                    : ($index === 0 ? true : null),
            ];
        }

        return $definitions;
    }

    /**
     * @return array{size: ?string, sku: ?string, price: ?float, compare_at_price: ?float, stock: ?int, barcode: ?string, is_default: ?bool}
     */
    protected static function singleDefinition(array $data, string $productSku, ?float $basePrice): array
    {
        return [
            'size' => $data['variant_size'] ?? null,
            'sku' => filled($data['variant_sku'] ?? null) ? Str::upper($data['variant_sku']) : null,
            'price' => ProductExcelRowParser::parseFloat($data['variant_price'] ?? null) ?? $basePrice,
            'compare_at_price' => ProductExcelRowParser::parseFloat($data['variant_compare_at_price'] ?? null),
            'stock' => ProductExcelRowParser::parseInt($data['variant_stock'] ?? null),
            'barcode' => $data['variant_barcode'] ?? null,
            'is_default' => ProductExcelRowParser::parseBool($data['variant_is_default'] ?? null),
        ];
    }
}
