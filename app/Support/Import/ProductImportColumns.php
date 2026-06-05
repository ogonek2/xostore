<?php

namespace App\Support\Import;

final class ProductImportColumns
{
    /** @var list<string> */
    public const REQUIRED = ['sku', 'name_pl'];

    /**
     * Ключи колонок шаблона (строка 1 Excel).
     *
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            'sku',
            'name_pl',
            'name_en',
            'slug_pl',
            'slug_en',
            'subtitle_pl',
            'subtitle_en',
            'short_description_pl',
            'short_description_en',
            'description_pl',
            'description_en',
            'fit_description_pl',
            'fit_description_en',
            'fabric_description_pl',
            'fabric_description_en',
            'meta_title_pl',
            'meta_title_en',
            'meta_description_pl',
            'meta_description_en',
            'status',
            'type',
            'brand_code',
            'primary_category_code',
            'category_codes',
            'catalog_codes',
            'tag_codes',
            'size_grid_code',
            'size_chart_preset_code',
            'base_price',
            'compare_at_price',
            'model_slug',
            'color_label',
            'color_slug',
            'color_hex',
            'is_featured',
            'is_new',
            'is_ready_to_ship',
            'custom_tailoring_enabled',
            'weight_grams',
            'sort_order',
            'published_at',
            'variants',
            'variant_sizes',
            'variant_prices',
            'variant_stocks',
            'variant_skus',
            'variant_compare_at_prices',
            'variant_barcodes',
            'variant_defaults',
            'variant_size',
            'variant_sku',
            'variant_price',
            'variant_compare_at_price',
            'variant_stock',
            'variant_barcode',
            'variant_is_default',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labelsPl(): array
    {
        return [
            'sku' => 'Артикул (SKU) *',
            'name_pl' => 'Название PL *',
            'name_en' => 'Название EN',
            'slug_pl' => 'Slug PL (необяз.; из name_pl, уникальный)',
            'slug_en' => 'Slug EN (необяз.; из name_en / name_pl)',
            'subtitle_pl' => 'Подзаголовок PL',
            'subtitle_en' => 'Подзаголовок EN',
            'short_description_pl' => 'Краткое описание PL',
            'short_description_en' => 'Краткое описание EN',
            'description_pl' => 'Описание PL',
            'description_en' => 'Описание EN',
            'fit_description_pl' => 'Посадка PL',
            'fit_description_en' => 'Посадка EN',
            'fabric_description_pl' => 'Ткань PL',
            'fabric_description_en' => 'Ткань EN',
            'meta_title_pl' => 'Meta title PL',
            'meta_title_en' => 'Meta title EN',
            'meta_description_pl' => 'Meta description PL',
            'meta_description_en' => 'Meta description EN',
            'status' => 'Статус (draft/published/archived)',
            'type' => 'Тип (simple/variable)',
            'brand_code' => 'Бренд (код/название, автосоздание)',
            'primary_category_code' => 'Основная категория (код/название)',
            'category_codes' => 'Категории (код/название, через запятую)',
            'catalog_codes' => 'Каталоги (код/название, через запятую)',
            'tag_codes' => 'Теги (код/название, через запятую)',
            'size_grid_code' => 'Пресет кнопок размера (код/название)',
            'size_chart_preset_code' => 'Пресет таблицы мерок (код/название)',
            'base_price' => 'Базовая цена',
            'compare_at_price' => 'Старая цена',
            'model_slug' => 'Slug модели (цветовые варианты)',
            'color_label' => 'Цвет',
            'color_slug' => 'Slug цвета',
            'color_hex' => 'HEX цвета (#RRGGBB)',
            'is_featured' => 'Рекомендованный (0/1)',
            'is_new' => 'Новинка (0/1)',
            'is_ready_to_ship' => 'В наличии (0/1)',
            'custom_tailoring_enabled' => 'Пошив на заказ (0/1)',
            'weight_grams' => 'Вес (г)',
            'sort_order' => 'Сортировка',
            'published_at' => 'Дата публикации',
            'variants' => 'Варианты: size:price:stock,… через запятую',
            'variant_sizes' => 'Размеры через запятую (s,m,l)',
            'variant_prices' => 'Цены через запятую (параллельно размерам)',
            'variant_stocks' => 'Остатки через запятую',
            'variant_skus' => 'SKU вариантов через запятую',
            'variant_compare_at_prices' => 'Старые цены вариантов через запятую',
            'variant_barcodes' => 'Штрихкоды через запятую',
            'variant_defaults' => 'По умолчанию: 1,0,0…',
            'variant_size' => 'Один размер или несколько через запятую',
            'variant_sku' => 'SKU варианта',
            'variant_price' => 'Цена варианта',
            'variant_compare_at_price' => 'Старая цена варианта',
            'variant_stock' => 'Остаток варианта',
            'variant_barcode' => 'Штрихкод варианта',
            'variant_is_default' => 'Вариант по умолчанию (0/1)',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function aliases(): array
    {
        return [
            'sku' => ['sku', 'артикул', 'artikul', 'article'],
            'name_pl' => ['name_pl', 'name', 'nazwa', 'название', 'nazwa_pl'],
        ];
    }
}
