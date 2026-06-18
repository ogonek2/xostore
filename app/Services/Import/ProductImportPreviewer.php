<?php

namespace App\Services\Import;

use App\Models\Product;
use App\Support\Import\ImportReferenceResolver;
use App\Support\Import\ProductImportModelSlugAnalyzer;
use App\Support\Import\ProductExcelRowParser;
use App\Support\Import\ProductExcelVariantParser;
use App\Support\Shop\ProductUniqueSlug;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductImportPreviewer
{
    public function __construct(
        protected ProductExcelImporter $importer,
    ) {}

    /**
     * @return array{
     *     errors: list<string>,
     *     warnings: list<string>,
     *     total_products: int,
     *     products: list<array<string, mixed>>
     * }
     */
    public function build(UploadedFile $file, int $limit = 25): array
    {
        $parsed = $this->importer->parseUploadedFile($file);

        if ($parsed['errors'] !== []) {
            return [
                'errors' => $parsed['errors'],
                'warnings' => [],
                'total_products' => 0,
                'products' => [],
            ];
        }

        $previewResult = ['created_references' => []];
        $resolver = new ImportReferenceResolver($previewResult);

        $products = [];

        foreach (array_slice($parsed['groups'], 0, $limit, true) as $groupKey => $entries) {
            $sku = $this->importer->resolveGroupSku($groupKey, $entries);
            $autoSku = ! str_starts_with($groupKey, 'sku:');
            $products[] = $this->previewProduct($sku, $entries, $resolver, $autoSku);
        }

        return [
            'errors' => [],
            'warnings' => ProductImportModelSlugAnalyzer::warnings($parsed['groups']),
            'total_products' => count($parsed['groups']),
            'products' => $products,
        ];
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     * @return array<string, mixed>
     */
    protected function previewProduct(string $sku, array $entries, ImportReferenceResolver $resolver, bool $autoSku = false): array
    {
        $data = $this->mergeProductData($entries);
        $lines = implode(', ', array_map(fn (array $entry): string => (string) $entry['line'], $entries));

        $existing = Product::withTrashed()->where('sku', $sku)->first();
        $product = $existing ?? new Product([
            'sku' => $sku,
            'color_slug' => filled($data['color_slug'] ?? null) ? Str::slug($data['color_slug']) : null,
            'model_slug' => filled($data['model_slug'] ?? null) ? Str::slug($data['model_slug']) : null,
        ]);

        if (array_key_exists('color_label', $data) && filled($data['color_label']) && blank($data['color_slug'] ?? null)) {
            $product->color_slug = Str::slug($data['color_label']);
        }

        $rowErrors = [];

        if (blank($data['name_pl'] ?? null)) {
            $rowErrors[] = 'Нет name_pl (название).';
        }

        $slugPl = filled($data['name_pl'] ?? null)
            ? ProductUniqueSlug::forImport($product, 'pl', $data['name_pl'], $data)
            : null;

        $slugEn = null;

        if (filled($data['name_en'] ?? null)) {
            $slugEn = ProductUniqueSlug::forImport($product, 'en', $data['name_en'], $data);
        } elseif (filled($data['name_pl'] ?? null)) {
            $slugEn = ProductUniqueSlug::forImport($product, 'en', $data['name_pl'], $data);
        }

        $variants = $this->previewVariants($entries, $sku, $data['base_price'] ?? null);

        return [
            'lines' => $lines,
            'sku' => $sku,
            'auto_sku' => $autoSku,
            'action' => $existing ? ($existing->trashed() ? 'restore' : 'update') : 'create',
            'name_pl' => $data['name_pl'] ?? null,
            'name_en' => $data['name_en'] ?? null,
            'slug_pl' => $slugPl,
            'slug_en' => $slugEn,
            'brand' => filled($data['brand_code'] ?? null)
                ? $resolver->previewBrand($data['brand_code'])
                : null,
            'primary_category' => filled($data['primary_category_code'] ?? null)
                ? $resolver->previewCategory($data['primary_category_code'])
                : null,
            'categories' => $this->previewList($resolver, 'category', $data['category_codes'] ?? null, $data['primary_category_code'] ?? null),
            'catalogs' => $this->previewList($resolver, 'catalog', $data['catalog_codes'] ?? null),
            'tags' => $this->previewList($resolver, 'tag', $data['tag_codes'] ?? null),
            'size_grid' => filled($data['size_grid_code'] ?? null)
                ? $resolver->previewSizeGrid($data['size_grid_code'])
                : null,
            'size_chart_preset' => filled($data['size_chart_preset_code'] ?? null)
                ? $resolver->previewSizeChartPreset($data['size_chart_preset_code'])
                : null,
            'color' => filled($data['color_code'] ?? null)
                ? $resolver->previewColor($data['color_code'], $data['color_hex'] ?? null)
                : (filled($data['color_label'] ?? null)
                    ? $resolver->previewColor($data['color_label'], $data['color_hex'] ?? null)
                    : null),
            'color_label' => $data['color_label'] ?? null,
            'color_code' => $data['color_code'] ?? null,
            'color_slug' => $product->color_slug,
            'model_slug' => ProductImportModelSlugAnalyzer::modelSlugFromEntries($entries),
            'variants' => $variants,
            'errors' => $rowErrors,
        ];
    }

    /**
     * @param  list<array{line: int, data: array<string, string>}>  $entries
     * @return list<array{size: ?string, sku: ?string, price: ?float, stock: ?int}>
     */
    protected function previewVariants(array $entries, string $sku, mixed $basePrice): array
    {
        $variants = [];
        $base = is_numeric($basePrice) ? (float) $basePrice : null;

        foreach ($entries as $entry) {
            if (! ProductExcelVariantParser::hasVariantInput($entry['data'])) {
                continue;
            }

            foreach (ProductExcelVariantParser::definitions($entry['data'], $sku, $base) as $definition) {
                $variants[] = [
                    'size' => $definition['size'] ?? null,
                    'sku' => $definition['sku'] ?? null,
                    'price' => $definition['price'] ?? null,
                    'stock' => $definition['stock'] ?? null,
                ];
            }
        }

        return $variants;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function previewList(
        ImportReferenceResolver $resolver,
        string $type,
        ?string $list,
        ?string $primary = null,
    ): array {
        $codes = ProductExcelRowParser::parseCodeList($list);
        $primary = trim((string) $primary);

        if ($primary !== '' && ! in_array($primary, $codes, true)) {
            array_unshift($codes, $primary);
        }

        $items = [];

        foreach ($codes as $code) {
            $preview = match ($type) {
                'category' => $resolver->previewCategory($code),
                'catalog' => $resolver->previewCatalog($code),
                'tag' => $resolver->previewTag($code),
                default => null,
            };

            if ($preview) {
                $preview['is_primary'] = $type === 'category'
                    && $primary !== ''
                    && strcasecmp($code, $primary) === 0;
                $items[] = $preview;
            }
        }

        return $items;
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
}
