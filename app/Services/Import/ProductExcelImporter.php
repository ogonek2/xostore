<?php

namespace App\Services\Import;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Brand;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeChartPreset;
use App\Models\SizeGrid;
use App\Models\SizeGridValue;
use App\Support\Import\ProductExcelRowParser;
use App\Support\Import\ProductImportColumns;
use App\Support\Shop\ProductVariantColorSync;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductExcelImporter
{
    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     variants_created: int,
     *     variants_updated: int,
     *     skipped: int,
     *     errors: list<string>,
     *     warnings: list<string>
     * }
     */
    public function import(UploadedFile $file): array
    {
        $result = [
            'created' => 0,
            'updated' => 0,
            'variants_created' => 0,
            'variants_updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $exception) {
            $result['errors'][] = 'Не удалось прочитать файл: '.$exception->getMessage();

            return $result;
        }

        $sheet = $spreadsheet->getSheetByName('Товары') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if ($rows === []) {
            $result['errors'][] = 'Файл пуст.';

            return $result;
        }

        $headerRowIndex = $this->findHeaderRowIndex($rows);

        if ($headerRowIndex === null) {
            $result['errors'][] = 'Не найдена строка заголовков (нужна колонка sku).';

            return $result;
        }

        $headerMap = ProductExcelRowParser::mapHeader($rows[$headerRowIndex]);

        if (! in_array('sku', $headerMap, true)) {
            $result['errors'][] = 'Обязательная колонка sku отсутствует в заголовке.';

            return $result;
        }

        /** @var array<string, list<array{line: int, data: array<string, string>}>> $groups */
        $groups = [];

        for ($line = $headerRowIndex + 1; $line < count($rows); $line++) {
            $row = $rows[$line];

            if (ProductExcelRowParser::rowIsEmpty($row)) {
                continue;
            }

            $data = ProductExcelRowParser::mapRow($headerMap, $row);

            if ($line === $headerRowIndex + 1) {
                $hint = Str::lower((string) ($data['sku'] ?? ''));

                if (str_contains($hint, '*') || str_contains($hint, 'артикул') || str_contains($hint, 'sku')) {
                    continue;
                }
            }

            $sku = Str::upper(trim((string) ($data['sku'] ?? '')));

            if ($sku === '') {
                $result['errors'][] = 'Строка '.($line + 1).': пустой артикул (sku).';
                $result['skipped']++;

                continue;
            }

            $data['sku'] = $sku;
            $groups[$sku][] = ['line' => $line + 1, 'data' => $data];
        }

        if ($groups === []) {
            $result['warnings'][] = 'Нет строк с данными для импорта.';

            return $result;
        }

        foreach ($groups as $sku => $entries) {
            try {
                $this->importProductGroup($sku, $entries, $result);
            } catch (\Throwable $exception) {
                $result['errors'][] = "Товар {$sku}: ".$exception->getMessage();
                $result['skipped']++;
            }
        }

        return $result;
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     * @param  array{created: int, updated: int, variants_created: int, variants_updated: int, skipped: int, errors: list<string>, warnings: list<string>}  $result
     */
    protected function importProductGroup(string $sku, array $entries, array &$result): void
    {
        $productData = $this->mergeProductData($entries);

        if (blank($productData['name_pl'] ?? null)) {
            $lines = implode(', ', array_map(fn (array $e) => (string) $e['line'], $entries));
            $result['errors'][] = "Товар {$sku} (строки {$lines}): обязательно поле name_pl (название).";
            $result['skipped']++;

            return;
        }

        DB::transaction(function () use ($sku, $productData, $entries, &$result): void {
            $product = Product::withTrashed()->where('sku', $sku)->first();
            $isNew = ! $product;

            if ($product?->trashed()) {
                $product->restore();
            }

            $attributes = $this->buildProductAttributes($productData, $sku, $isNew);

            if ($isNew) {
                $product = Product::query()->create($attributes);
                $result['created']++;
            } else {
                $product->update($attributes);
                $result['updated']++;
            }

            $this->syncTranslations($product, $productData);
            $this->syncRelations($product, $productData, $result);
            $this->importVariants($product, $entries, $result);

            ProductVariantColorSync::syncProductVariants($product);
        });
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     * @return array<string, string>
     */
    protected function mergeProductData(array $entries): array
    {
        $merged = [];

        foreach ($entries as $entry) {
            foreach ($entry['data'] as $key => $value) {
                if (str_starts_with($key, 'variant_')) {
                    continue;
                }

                if (! array_key_exists($key, $merged) || $merged[$key] === '') {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, mixed>
     */
    protected function buildProductAttributes(array $data, string $sku, bool $isNew): array
    {
        $attributes = ['sku' => $sku];

        if (array_key_exists('status', $data)) {
            $attributes['status'] = ProductStatus::tryFrom(Str::lower($data['status']))?->value
                ?? ProductStatus::Draft->value;
        } elseif ($isNew) {
            $attributes['status'] = ProductStatus::Draft->value;
        }

        if (array_key_exists('type', $data)) {
            $attributes['type'] = ProductType::tryFrom(Str::lower($data['type']))?->value
                ?? ProductType::Variable->value;
        } elseif ($isNew) {
            $attributes['type'] = ProductType::Variable->value;
        }

        if (array_key_exists('brand_code', $data)) {
            $attributes['brand_id'] = $this->resolveBrandId($data['brand_code']);
        }

        if (array_key_exists('size_grid_code', $data)) {
            $attributes['size_grid_id'] = $this->resolveSizeGridId($data['size_grid_code']);
        }

        if (array_key_exists('size_chart_preset_code', $data)) {
            $attributes['size_chart_preset_id'] = $this->resolveSizeChartPresetId($data['size_chart_preset_code']);
        }

        if (array_key_exists('base_price', $data)) {
            $attributes['base_price'] = ProductExcelRowParser::parseFloat($data['base_price']);
        }

        if (array_key_exists('compare_at_price', $data)) {
            $attributes['compare_at_price'] = ProductExcelRowParser::parseFloat($data['compare_at_price']);
        }

        if (array_key_exists('model_slug', $data)) {
            $attributes['model_slug'] = filled($data['model_slug']) ? Str::slug($data['model_slug']) : null;
        }

        if (array_key_exists('color_label', $data)) {
            $attributes['color_label'] = $data['color_label'];
        }

        if (array_key_exists('color_slug', $data)) {
            $attributes['color_slug'] = filled($data['color_slug']) ? Str::slug($data['color_slug']) : null;
        } elseif (array_key_exists('color_label', $data) && filled($data['color_label'])) {
            $attributes['color_slug'] = Str::slug($data['color_label']);
        }

        if (array_key_exists('color_hex', $data)) {
            $attributes['color_hex'] = $this->normalizeHex($data['color_hex']);
        }

        foreach (['is_featured', 'is_new', 'is_ready_to_ship', 'custom_tailoring_enabled'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $attributes[$flag] = ProductExcelRowParser::parseBool($data[$flag]) ?? false;
            }
        }

        if (array_key_exists('weight_grams', $data)) {
            $attributes['weight_grams'] = ProductExcelRowParser::parseInt($data['weight_grams']);
        }

        if (array_key_exists('sort_order', $data)) {
            $attributes['sort_order'] = ProductExcelRowParser::parseInt($data['sort_order']) ?? 0;
        } elseif ($isNew) {
            $attributes['sort_order'] = 0;
        }

        if (array_key_exists('published_at', $data)) {
            $attributes['published_at'] = ProductExcelRowParser::parseDate($data['published_at']);
        }

        if (array_key_exists('primary_category_code', $data)) {
            $attributes['primary_category_id'] = $this->resolveCategoryId($data['primary_category_code']);
        }

        return $attributes;
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function syncTranslations(Product $product, array $data): void
    {
        $pl = Language::query()->where('code', 'pl')->first();
        $en = Language::query()->where('code', 'en')->first();

        $fields = [
            'name', 'slug', 'subtitle', 'short_description', 'description',
            'fit_description', 'fabric_description', 'meta_title', 'meta_description',
        ];

        foreach ($fields as $field) {
            if ($pl && filled($data["{$field}_pl"] ?? null)) {
                $value = $data["{$field}_pl"];

                if ($field === 'slug') {
                    $value = Str::slug($value);
                }

                $product->setTranslation($field, $value, $pl);
            }

            if ($en && filled($data["{$field}_en"] ?? null)) {
                $value = $data["{$field}_en"];

                if ($field === 'slug') {
                    $value = Str::slug($value);
                }

                $product->setTranslation($field, $value, $en);
            }
        }

        if ($pl && filled($data['name_pl']) && blank($data['slug_pl'] ?? null)) {
            $product->setTranslation('slug', Str::slug($data['name_pl']), $pl);
        }
    }

    /**
     * @param  array<string, string>  $data
     * @param  array{warnings: list<string>}  $result
     */
    protected function syncRelations(Product $product, array $data, array &$result): void
    {
        $categoryCodes = ProductExcelRowParser::parseCodeList($data['category_codes'] ?? null);
        $primaryCode = trim((string) ($data['primary_category_code'] ?? ''));

        if ($primaryCode !== '' && ! in_array($primaryCode, $categoryCodes, true)) {
            array_unshift($categoryCodes, $primaryCode);
        }

        if ($categoryCodes !== []) {
            $sync = [];
            $primaryId = null;

            foreach ($categoryCodes as $code) {
                $category = Category::query()->where('code', $code)->first();

                if (! $category) {
                    $result['warnings'][] = "Товар {$product->sku}: категория «{$code}» не найдена.";

                    continue;
                }

                $isPrimary = $primaryCode !== ''
                    ? $code === $primaryCode
                    : $category->id === ($product->primary_category_id ?? $category->id);

                $sync[$category->id] = ['is_primary' => $isPrimary];

                if ($isPrimary) {
                    $primaryId = $category->id;
                }
            }

            if ($sync !== []) {
                $product->categories()->sync($sync);

                if ($primaryId) {
                    $product->update(['primary_category_id' => $primaryId]);
                }
            }
        }

        $catalogCodes = ProductExcelRowParser::parseCodeList($data['catalog_codes'] ?? null);

        if ($catalogCodes !== []) {
            $catalogIds = [];

            foreach ($catalogCodes as $code) {
                $catalog = Catalog::query()->where('code', $code)->first();

                if (! $catalog) {
                    $result['warnings'][] = "Товар {$product->sku}: каталог «{$code}» не найден.";

                    continue;
                }

                $catalogIds[] = $catalog->id;
            }

            if ($catalogIds !== []) {
                $product->catalogs()->sync($catalogIds);
            }
        }
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     * @param  array{variants_created: int, variants_updated: int, warnings: list<string>, errors: list<string>}  $result
     */
    protected function importVariants(Product $product, array $entries, array &$result): void
    {
        $hasVariantRows = false;

        foreach ($entries as $entry) {
            $row = $entry['data'];
            $line = $entry['line'];

            if (! $this->rowHasVariantData($row)) {
                continue;
            }

            $hasVariantRows = true;

            $variantSku = Str::upper(trim((string) ($row['variant_sku'] ?? '')));

            if ($variantSku === '') {
                $sizePart = Str::upper(trim((string) ($row['variant_size'] ?? 'ONE')));
                $variantSku = "{$product->sku}-{$sizePart}";
            }

            $price = ProductExcelRowParser::parseFloat($row['variant_price'] ?? null)
                ?? $product->base_price
                ?? 0;

            $sizeGridValueId = $this->resolveSizeGridValueId(
                $product->size_grid_id,
                $row['variant_size'] ?? null,
            );

            if (filled($row['variant_size'] ?? null) && ! $sizeGridValueId) {
                $result['warnings'][] = "Строка {$line}: размер «{$row['variant_size']}» не найден в пресете товара.";
            }

            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('sku', $variantSku)
                ->first();

            $payload = array_filter([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'price' => $price,
                'compare_at_price' => ProductExcelRowParser::parseFloat($row['variant_compare_at_price'] ?? null),
                'stock_qty' => ProductExcelRowParser::parseInt($row['variant_stock'] ?? null) ?? 0,
                'size_grid_value_id' => $sizeGridValueId,
                'barcode' => $row['variant_barcode'] ?? null,
                'is_default' => ProductExcelRowParser::parseBool($row['variant_is_default'] ?? null) ?? false,
                'is_active' => true,
            ], fn ($value) => $value !== null);

            if ($variant) {
                $variant->update($payload);
                $result['variants_updated']++;
            } else {
                ProductVariant::query()->create($payload);
                $result['variants_created']++;
            }
        }

        if (! $hasVariantRows && $product->base_price) {
            $defaultSku = "{$product->sku}-DEFAULT";
            $exists = $product->variants()->where('sku', $defaultSku)->exists();

            if (! $exists) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'sku' => $defaultSku,
                    'price' => $product->base_price,
                    'compare_at_price' => $product->compare_at_price,
                    'is_default' => true,
                    'is_active' => true,
                    'stock_qty' => 0,
                ]);
                $result['variants_created']++;
            }
        }
    }

    /**
     * @param  array<string, string>  $row
     */
    protected function rowHasVariantData(array $row): bool
    {
        return filled($row['variant_size'] ?? null)
            || filled($row['variant_sku'] ?? null)
            || filled($row['variant_price'] ?? null)
            || filled($row['variant_stock'] ?? null);
    }

    /**
     * @param  list<list<mixed>>  $rows
     */
    protected function findHeaderRowIndex(array $rows): ?int
    {
        $limit = min(count($rows), 10);

        for ($i = 0; $i < $limit; $i++) {
            $normalized = array_map(
                fn ($cell) => Str::lower(trim((string) $cell)),
                $rows[$i],
            );

            if (in_array('sku', $normalized, true) || in_array('артикул', $normalized, true)) {
                return $i;
            }
        }

        return null;
    }

    protected function resolveBrandId(?string $code): ?int
    {
        if (blank($code)) {
            return null;
        }

        return Brand::query()->where('code', trim($code))->value('id');
    }

    protected function resolveCategoryId(?string $code): ?int
    {
        if (blank($code)) {
            return null;
        }

        return Category::query()->where('code', trim($code))->value('id');
    }

    protected function resolveSizeGridId(?string $code): ?int
    {
        if (blank($code)) {
            return null;
        }

        return SizeGrid::query()->where('code', trim($code))->where('is_active', true)->value('id');
    }

    protected function resolveSizeChartPresetId(?string $code): ?int
    {
        if (blank($code)) {
            return null;
        }

        return SizeChartPreset::query()->where('code', trim($code))->where('is_active', true)->value('id');
    }

    protected function resolveSizeGridValueId(?int $sizeGridId, ?string $sizeCode): ?int
    {
        if (! $sizeGridId || blank($sizeCode)) {
            return null;
        }

        $code = Str::lower(trim($sizeCode));

        return SizeGridValue::query()
            ->where('size_grid_id', $sizeGridId)
            ->where(function ($query) use ($code): void {
                $query->where('value', $code)
                    ->orWhereRaw('LOWER(display_value) = ?', [$code]);
            })
            ->value('id');
    }

    protected function normalizeHex(?string $hex): ?string
    {
        if (blank($hex)) {
            return null;
        }

        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return '#'.Str::lower($hex);
    }
}
