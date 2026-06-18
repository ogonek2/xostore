<?php

namespace App\Support\Import;

use App\Enums\CatalogType;
use App\Enums\CategoryType;
use App\Models\Brand;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Color;
use App\Models\Language;
use App\Models\SizeChartPreset;
use App\Models\SizeGrid;
use App\Models\SizeGridValue;
use App\Models\Tag;
use App\Support\Shop\ProductColorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImportReferenceResolver
{
    /** @var array<string, Model> */
    protected array $cache = [];

    /**
     * @param  array{
     *     warnings?: list<string>,
     *     created_references?: list<string>
     * }  $result
     */
    public function __construct(protected array &$result) {}

    public function findOrCreateCategory(string $input): ?Category
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return $this->remember('category', $input, function () use ($input): Category {
            $existing = $this->findByCodeOrName(Category::class, $input);

            if ($existing instanceof Category) {
                return $existing;
            }

            $code = ImportUniqueCode::fromLabel(
                $input,
                fn (string $candidate): bool => Category::query()->where('code', $candidate)->exists(),
            );

            $type = CategoryType::tryFrom(Str::lower($code))
                ?? CategoryType::tryFrom(ImportUniqueCode::slugBase($input))
                ?? CategoryType::Unisex;

            $category = Category::query()->create([
                'code' => $code,
                'type' => $type->value,
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 0,
            ]);

            $this->applyNameTranslations($category, $input, hasSlug: true);
            $this->logCreated('Категория', $input, $code);

            return $category;
        });
    }

    public function findOrCreateCatalog(string $input): ?Catalog
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return $this->remember('catalog', $input, function () use ($input): Catalog {
            $existing = $this->findByCodeOrName(Catalog::class, $input);

            if ($existing instanceof Catalog) {
                return $existing;
            }

            $code = ImportUniqueCode::fromLabel(
                $input,
                fn (string $candidate): bool => Catalog::query()->where('code', $candidate)->exists(),
            );

            $catalog = Catalog::query()->create([
                'code' => $code,
                'type' => CatalogType::Manual->value,
                'is_active' => true,
                'show_on_homepage' => false,
                'sort_order' => 0,
            ]);

            $this->applyNameTranslations($catalog, $input, hasSlug: true);
            $this->logCreated('Каталог', $input, $code);

            return $catalog;
        });
    }

    public function findOrCreateTag(string $input): ?Tag
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return $this->remember('tag', $input, function () use ($input): Tag {
            $existing = $this->findByCodeOrName(Tag::class, $input);

            if ($existing instanceof Tag) {
                return $existing;
            }

            $code = ImportUniqueCode::fromLabel(
                $input,
                fn (string $candidate): bool => Tag::query()->where('code', $candidate)->exists(),
            );

            $tag = Tag::query()->create([
                'code' => $code,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            $this->applyNameTranslations($tag, $input, hasSlug: false);
            $this->logCreated('Тег', $input, $code);

            return $tag;
        });
    }

    public function findOrCreateBrand(string $input): ?Brand
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return $this->remember('brand', $input, function () use ($input): Brand {
            $existing = $this->findByCodeOrName(Brand::class, $input);

            if ($existing instanceof Brand) {
                return $existing;
            }

            $code = ImportUniqueCode::fromLabel(
                $input,
                fn (string $candidate): bool => Brand::query()->where('code', $candidate)->exists(),
            );

            $brand = Brand::query()->create([
                'code' => $code,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            $this->applyNameTranslations($brand, $input, hasSlug: true);
            $this->logCreated('Бренд', $input, $code);

            return $brand;
        });
    }

    public function findColor(string $input): ?Color
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return ProductColorService::findByCodeOrName($input);
    }

    public function findOrCreateColor(string $input, ?string $hex = null): ?Color
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        $cacheKey = 'color:'.Str::lower($input).':'.Str::lower((string) $hex);

        return $this->remember('color', $cacheKey, function () use ($input, $hex): Color {
            $existing = ProductColorService::findByCodeOrName($input);

            if ($existing) {
                $normalizedHex = ProductColorService::normalizeHex($hex);

                if ($normalizedHex && $existing->hex !== $normalizedHex) {
                    $existing->update(['hex' => $normalizedHex]);
                }

                return $existing;
            }

            $color = ProductColorService::createFromPlName($input, $hex);
            $this->logCreated('Цвет', $input, $color->code);

            return $color;
        });
    }

    public function findOrCreateSizeGrid(string $input): ?SizeGrid
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return $this->remember('size_grid', $input, function () use ($input): SizeGrid {
            $existing = $this->findByCodeOrName(SizeGrid::class, $input);

            if ($existing instanceof SizeGrid) {
                return $existing;
            }

            $code = ImportUniqueCode::fromLabel(
                $input,
                fn (string $candidate): bool => SizeGrid::query()->where('code', $candidate)->exists(),
            );

            $grid = SizeGrid::query()->create([
                'code' => $code,
                'unit' => null,
                'is_active' => true,
            ]);

            $this->applyNameTranslations($grid, $input, hasSlug: false);
            $this->logCreated('Пресет размеров', $input, $code);

            return $grid;
        });
    }

    public function findOrCreateSizeChartPreset(string $input): ?SizeChartPreset
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        return $this->remember('size_chart_preset', $input, function () use ($input): SizeChartPreset {
            $existing = $this->findByCodeOrName(SizeChartPreset::class, $input);

            if ($existing instanceof SizeChartPreset) {
                return $existing;
            }

            $code = ImportUniqueCode::fromLabel(
                $input,
                fn (string $candidate): bool => SizeChartPreset::query()->where('code', $candidate)->exists(),
            );

            $preset = SizeChartPreset::query()->create([
                'code' => $code,
                'unit' => 'cm',
                'is_active' => true,
            ]);

            $this->applyNameTranslations($preset, $input, hasSlug: false);
            $this->logCreated('Пресет таблицы мерок', $input, $code);

            return $preset;
        });
    }

    public function findOrCreateSizeGridValue(?int $sizeGridId, ?string $sizeCode): ?int
    {
        if (! $sizeGridId || blank($sizeCode)) {
            return null;
        }

        $sizeCode = trim($sizeCode);
        $normalized = Str::lower($sizeCode);
        $cacheKey = "size_grid_value:{$sizeGridId}:{$normalized}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey]->id;
        }

        $existing = SizeGridValue::query()
            ->where('size_grid_id', $sizeGridId)
            ->where(function ($query) use ($normalized, $sizeCode): void {
                $query->where('value', $normalized)
                    ->orWhereRaw('LOWER(display_value) = ?', [$normalized])
                    ->orWhereRaw('LOWER(display_value) = ?', [Str::lower($sizeCode)]);
            })
            ->first();

        if ($existing) {
            $this->cache[$cacheKey] = $existing;

            return $existing->id;
        }

        $maxSort = (int) SizeGridValue::query()
            ->where('size_grid_id', $sizeGridId)
            ->max('sort_order');

        $display = strlen($sizeCode) <= 3 ? Str::upper($sizeCode) : $sizeCode;

        $value = ImportUniqueCode::slugBase($normalized);
        if ($value === 'item') {
            $value = $normalized;
        }

        $value = substr($value, 0, 32);

        if (SizeGridValue::query()->where('size_grid_id', $sizeGridId)->where('value', $value)->exists()) {
            $value = ImportUniqueCode::fromLabel(
                $sizeCode,
                fn (string $candidate): bool => SizeGridValue::query()
                    ->where('size_grid_id', $sizeGridId)
                    ->where('value', $candidate)
                    ->exists(),
            );
            $value = substr($value, 0, 32);
        }

        $created = SizeGridValue::query()->create([
            'size_grid_id' => $sizeGridId,
            'value' => $value,
            'display_value' => $display,
            'sort_order' => $maxSort + 1,
        ]);

        $this->cache[$cacheKey] = $created;
        $this->logCreated('Размер в пресете', $sizeCode, $value);

        return $created->id;
    }

    /**
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    public function previewCategory(string $input): ?array
    {
        return $this->previewEntity(Category::class, $input);
    }

    /**
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    public function previewCatalog(string $input): ?array
    {
        return $this->previewEntity(Catalog::class, $input);
    }

    /**
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    public function previewTag(string $input): ?array
    {
        return $this->previewEntity(Tag::class, $input);
    }

    /**
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    public function previewBrand(string $input): ?array
    {
        return $this->previewEntity(Brand::class, $input);
    }

    /**
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    public function previewColor(string $input, ?string $hex = null): ?array
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        $existing = ProductColorService::findByCodeOrName($input);

        if ($existing) {
            return [
                'input' => $input,
                'code' => (string) $existing->code,
                'name' => $existing->translate('name', 'pl') ?? $existing->code,
                'exists' => true,
            ];
        }

        $code = ImportUniqueCode::fromLabel(
            $input,
            fn (string $candidate): bool => Color::query()->where('code', $candidate)->exists(),
        );

        return [
            'input' => $input,
            'code' => $code,
            'name' => $input,
            'exists' => false,
            'will_create' => true,
            'hex' => ProductColorService::normalizeHex($hex),
        ];
    }

    /**
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    public function previewSizeGrid(string $input): ?array
    {
        return $this->previewEntity(SizeGrid::class, $input);
    }

    /**
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    public function previewSizeChartPreset(string $input): ?array
    {
        return $this->previewEntity(SizeChartPreset::class, $input);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array{input: string, code: string, name: string, exists: bool, will_create?: bool}|null
     */
    protected function previewEntity(string $modelClass, string $input): ?array
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        $existing = $this->findByCodeOrName($modelClass, $input);

        if ($existing) {
            return [
                'input' => $input,
                'code' => (string) $existing->code,
                'name' => method_exists($existing, 'translate')
                    ? ($existing->translate('name', 'pl') ?? $existing->code)
                    : (string) $existing->code,
                'exists' => true,
            ];
        }

        $code = ImportUniqueCode::fromLabel(
            $input,
            fn (string $candidate): bool => $modelClass::query()->where('code', $candidate)->exists(),
        );

        return [
            'input' => $input,
            'code' => $code,
            'name' => $input,
            'exists' => false,
            'will_create' => true,
        ];
    }

    /**
     * @template T of Model
     *
     * @param  class-string<T>  $modelClass
     * @return T|null
     */
    protected function findByCodeOrName(string $modelClass, string $input): ?Model
    {
        $code = Str::lower(trim($input));

        $byCode = $modelClass::query()
            ->whereRaw('LOWER(code) = ?', [$code])
            ->first();

        if ($byCode) {
            return $byCode;
        }

        $languageIds = Language::query()
            ->whereIn('code', ['pl', 'en'])
            ->pluck('id')
            ->all();

        if ($languageIds === []) {
            return null;
        }

        $needle = Str::lower(trim($input));

        return $modelClass::query()
            ->whereHas('translates', function ($query) use ($languageIds, $needle): void {
                $query->whereIn('language_id', $languageIds)
                    ->whereIn('field', ['name', 'slug'])
                    ->whereRaw('LOWER(value) = ?', [$needle]);
            })
            ->first();
    }

    protected function applyNameTranslations(Model $model, string $label, bool $hasSlug): void
    {
        $pl = Language::query()->where('code', 'pl')->first();

        if (! $pl) {
            return;
        }

        if (method_exists($model, 'setTranslation')) {
            $model->setTranslation('name', $label, $pl);

            if ($hasSlug) {
                $model->setTranslation('slug', ImportUniqueCode::slugBase($label), $pl);
            }
        }
    }

    protected function logCreated(string $type, string $label, string $code): void
    {
        if (! isset($this->result['created_references'])) {
            $this->result['created_references'] = [];
        }

        $this->result['created_references'][] = "{$type} «{$label}» (код: {$code})";
    }

    /**
     * @template T of Model
     *
     * @param  callable(): T  $factory
     * @return T
     */
    protected function remember(string $type, string $input, callable $factory): Model
    {
        $key = "{$type}:".Str::lower(trim($input));

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $model = $factory();
        $this->cache[$key] = $model;

        return $model;
    }
}
