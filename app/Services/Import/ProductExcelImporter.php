<?php

namespace App\Services\Import;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Color;
use App\Support\Import\ImportReferenceResolver;
use App\Support\Import\ProductImportModelSlugAnalyzer;
use App\Support\Import\ProductExcelRowParser;
use App\Support\Import\ProductImportSpreadsheetLoader;
use App\Support\Import\ProductExcelVariantParser;
use App\Support\Import\ProductImportColumns;
use App\Support\Import\ProductImportTemplateRowDetector;
use App\Support\Shop\ProductSkuGenerator;
use App\Support\Shop\ProductUniqueSlug;
use App\Support\Shop\ProductVariantColorSync;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ProductExcelImporter
{
    protected ?ImportReferenceResolver $referenceResolver = null;

    /** @var array{warnings: list<string>, created_references: list<string>} */
    protected array $fallbackReferenceResult = [
        'warnings' => [],
        'created_references' => [],
    ];

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     variants_created: int,
     *     variants_updated: int,
     *     skipped: int,
     *     errors: list<string>,
     *     warnings: list<string>,
     *     created_references: list<string>
     * }
     */
    /**
     * @return array{
     *     groups: array<string, list<array{line: int, data: array<string, string>}>>,
     *     errors: list<string>,
     *     warnings: list<string>,
     *     skipped: int
     * }
     */
    public function parseUploadedFile(UploadedFile $file): array
    {
        $result = [
            'groups' => [],
            'errors' => [],
            'warnings' => [],
            'skipped' => 0,
        ];

        try {
            $spreadsheet = ProductImportSpreadsheetLoader::load($file);
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
            $result['errors'][] = 'Не найдена строка заголовков (нужна колонка sku или name_pl).';

            return $result;
        }

        $headerMap = ProductExcelRowParser::mapHeader($rows[$headerRowIndex]);

        if (
            ! in_array('sku', $headerMap, true)
            && ! in_array('name_pl', $headerMap, true)
        ) {
            $result['errors'][] = 'В заголовке нужна колонка sku или name_pl (название PL).';

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

            if (ProductImportTemplateRowDetector::isMetaRow($data, $line - $headerRowIndex)) {
                continue;
            }

            $sku = Str::upper(trim((string) ($data['sku'] ?? '')));
            $namePl = trim((string) ($data['name_pl'] ?? ''));

            if ($sku === '' && $namePl === '') {
                $result['errors'][] = 'Строка '.($line + 1).': укажите sku или name_pl (название).';
                $result['skipped']++;

                continue;
            }

            $groupKey = $sku !== '' ? "sku:{$sku}" : 'name:'.Str::lower($namePl);
            $data['sku'] = $sku;
            $groups[$groupKey][] = ['line' => $line + 1, 'data' => $data];
        }

        if ($groups === []) {
            $result['warnings'][] = 'Нет строк с данными для импорта.';
        }

        $result['groups'] = $groups;

        return $result;
    }

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
            'created_references' => [],
        ];

        $this->referenceResolver = new ImportReferenceResolver($result);

        $parsed = $this->parseUploadedFile($file);
        $result['errors'] = [...$result['errors'], ...$parsed['errors']];
        $result['warnings'] = [...$result['warnings'], ...$parsed['warnings']];
        $result['skipped'] += $parsed['skipped'];

        $result['warnings'] = [
            ...$result['warnings'],
            ...ProductImportModelSlugAnalyzer::warnings($parsed['groups']),
        ];

        if ($parsed['errors'] !== [] || $parsed['groups'] === []) {
            return $result;
        }

        foreach ($parsed['groups'] as $groupKey => $entries) {
            try {
                $sku = $this->resolveGroupSku($groupKey, $entries);
                $this->importProductGroup($sku, $entries, $result);
            } catch (\Throwable $exception) {
                $label = str_starts_with($groupKey, 'sku:')
                    ? substr($groupKey, 4)
                    : ($entries[0]['data']['name_pl'] ?? $groupKey);
                $result['errors'][] = "Товар {$label}: ".$exception->getMessage();
                $result['skipped']++;
            }
        }

        return $result;
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     */
    public function resolveGroupSku(string $groupKey, array $entries): string
    {
        if (str_starts_with($groupKey, 'sku:')) {
            return substr($groupKey, 4);
        }

        $namePl = trim((string) ($entries[0]['data']['name_pl'] ?? ''));

        return ProductSkuGenerator::generate($namePl !== '' ? $namePl : null);
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
            $attributes['brand_id'] = $this->referenceResolver()
                ->findOrCreateBrand($data['brand_code'])
                ?->id;
        }

        if (array_key_exists('size_grid_code', $data)) {
            $attributes['size_grid_id'] = $this->referenceResolver()
                ->findOrCreateSizeGrid($data['size_grid_code'])
                ?->id;
        }

        if (array_key_exists('size_chart_preset_code', $data)) {
            $attributes['size_chart_preset_id'] = $this->referenceResolver()
                ->findOrCreateSizeChartPreset($data['size_chart_preset_code'])
                ?->id;
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

        $color = $this->resolveImportColor($data);

        if ($color) {
            $defaultLocale = (string) config('shop.default_language', 'pl');
            $attributes['color_id'] = $color->id;
            $attributes['color_label'] = $color->translate('name', $defaultLocale) ?? $color->code;
            $attributes['color_slug'] = $color->code;
            $attributes['color_hex'] = $color->hex;
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
            $attributes['primary_category_id'] = $this->referenceResolver()
                ->findOrCreateCategory($data['primary_category_code'])
                ?->id;
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
            if ($field === 'slug') {
                continue;
            }

            if ($pl && filled($data["{$field}_pl"] ?? null)) {
                $product->setTranslation($field, $data["{$field}_pl"], $pl);
            }

            if ($en && filled($data["{$field}_en"] ?? null)) {
                $product->setTranslation($field, $data["{$field}_en"], $en);
            }
        }

        if ($pl && filled($data['name_pl'] ?? null)) {
            $slug = ProductUniqueSlug::forImport($product, 'pl', $data['name_pl'], $data);
            $product->setTranslation('slug', $slug, $pl);
        }

        if ($en && filled($data['name_en'] ?? null)) {
            $slug = ProductUniqueSlug::forImport($product, 'en', $data['name_en'], $data);
            $product->setTranslation('slug', $slug, $en);
        } elseif ($en && filled($data['name_pl'] ?? null) && blank($data['slug_en'] ?? null)) {
            $slug = ProductUniqueSlug::forImport($product, 'en', $data['name_pl'], $data);
            $product->setTranslation('slug', $slug, $en);
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
                $category = $this->referenceResolver()->findOrCreateCategory($code);

                if (! $category) {
                    continue;
                }

                $isPrimary = $primaryCode !== ''
                    ? strcasecmp($code, $primaryCode) === 0
                    : $category->id === ($product->primary_category_id ?? $category->id);

                $sync[$category->id] = [
                    'is_primary' => ($sync[$category->id]['is_primary'] ?? false) || $isPrimary,
                ];

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
                $catalog = $this->referenceResolver()->findOrCreateCatalog($code);

                if (! $catalog) {
                    continue;
                }

                $catalogIds[] = $catalog->id;
            }

            if ($catalogIds !== []) {
                $product->catalogs()->sync($catalogIds);
            }
        }

        $tagCodes = ProductExcelRowParser::parseCodeList($data['tag_codes'] ?? null);

        if ($tagCodes !== []) {
            $tagIds = [];

            foreach ($tagCodes as $code) {
                $tag = $this->referenceResolver()->findOrCreateTag($code);

                if (! $tag) {
                    continue;
                }

                $tagIds[] = $tag->id;
            }

            if ($tagIds !== []) {
                $product->tags()->sync($tagIds);
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
        $basePrice = $product->base_price ? (float) $product->base_price : null;

        foreach ($entries as $entry) {
            $row = $entry['data'];
            $line = $entry['line'];

            if (! ProductExcelVariantParser::hasVariantInput($row)) {
                continue;
            }

            $definitions = ProductExcelVariantParser::definitions($row, $product->sku, $basePrice);

            if ($definitions === []) {
                continue;
            }

            $hasVariantRows = true;

            foreach ($definitions as $index => $definition) {
                $this->upsertVariantDefinition($product, $definition, $line, $index, $result);
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
     * @param  array{
     *     size: ?string,
     *     sku: ?string,
     *     price: ?float,
     *     compare_at_price: ?float,
     *     stock: ?int,
     *     barcode: ?string,
     *     is_default: ?bool
     * }  $definition
     * @param  array{variants_created: int, variants_updated: int, warnings: list<string>}  $result
     */
    protected function upsertVariantDefinition(
        Product $product,
        array $definition,
        int $line,
        int $index,
        array &$result,
    ): void {
        $variantSku = $definition['sku'] ?? null;

        if (blank($variantSku)) {
            $sizePart = Str::upper(trim((string) ($definition['size'] ?? 'ONE')));
            $variantSku = "{$product->sku}-{$sizePart}";
        }

        $sizeGridValueId = $this->referenceResolver()->findOrCreateSizeGridValue(
            $product->size_grid_id,
            $definition['size'] ?? null,
        );

        if (filled($definition['size'] ?? null) && ! $sizeGridValueId && ! $product->size_grid_id) {
            $grid = $this->referenceResolver()->findOrCreateSizeGrid("import-{$product->sku}");
            $product->update(['size_grid_id' => $grid->id]);
            $sizeGridValueId = $this->referenceResolver()->findOrCreateSizeGridValue(
                $grid->id,
                $definition['size'] ?? null,
            );
        }

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('sku', $variantSku)
            ->first();

        $payload = [
            'product_id' => $product->id,
            'sku' => $variantSku,
            'price' => $definition['price'] ?? $product->base_price ?? 0,
            'compare_at_price' => $definition['compare_at_price'],
            'stock_qty' => $definition['stock'] ?? 0,
            'size_grid_value_id' => $sizeGridValueId,
            'barcode' => $definition['barcode'],
            'is_default' => $definition['is_default'] ?? ($index === 0),
            'is_active' => true,
        ];

        if ($variant) {
            $variant->update($payload);
            $result['variants_updated']++;
        } else {
            ProductVariant::query()->create($payload);
            $result['variants_created']++;
        }
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

            if (in_array('name_pl', $normalized, true) || in_array('nazwa', $normalized, true) || in_array('название', $normalized, true)) {
                return $i;
            }
        }

        return null;
    }

    protected function referenceResolver(): ImportReferenceResolver
    {
        return $this->referenceResolver ??= new ImportReferenceResolver($this->fallbackReferenceResult);
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function resolveImportColor(array $data): ?Color
    {
        $resolver = $this->referenceResolver();
        $hex = array_key_exists('color_hex', $data) ? $data['color_hex'] : null;

        if (array_key_exists('color_code', $data) && filled($data['color_code'])) {
            return $resolver->findColor($data['color_code'])
                ?? $resolver->findOrCreateColor($data['color_code'], $hex);
        }

        if (array_key_exists('color_label', $data) && filled($data['color_label'])) {
            return $resolver->findOrCreateColor($data['color_label'], $hex);
        }

        return null;
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
