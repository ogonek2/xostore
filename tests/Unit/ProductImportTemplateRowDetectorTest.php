<?php

namespace Tests\Unit;

use App\Support\Import\ProductImportColumns;
use App\Support\Import\ProductImportTemplateRowDetector;
use Tests\TestCase;

class ProductImportTemplateRowDetectorTest extends TestCase
{
    public function test_detects_template_label_row(): void
    {
        $labels = ProductImportColumns::labelsPl();

        $data = [
            'sku' => $labels['sku'],
            'name_pl' => $labels['name_pl'],
            'name_en' => $labels['name_en'],
            'slug_pl' => $labels['slug_pl'],
            'slug_en' => $labels['slug_en'],
            'brand_code' => $labels['brand_code'],
        ];

        $this->assertTrue(ProductImportTemplateRowDetector::isMetaRow($data, 1));
    }

    public function test_detects_garbled_label_row_with_placeholder_slugs(): void
    {
        $data = [
            'name_pl' => '???????? PL *',
            'name_en' => '???????? EN',
            'slug_pl' => 'pl',
            'slug_en' => 'en',
            'model_slug' => 'slug',
            'brand_code' => '????? (item)',
        ];

        $this->assertTrue(ProductImportTemplateRowDetector::isMetaRow($data, 1));
    }

    public function test_detects_example_status_row(): void
    {
        $data = [
            'sku' => 'DRESS-001-BLACK',
            'name_pl' => 'Sukienka wieczorowa',
            'status' => 'example',
        ];

        $this->assertTrue(ProductImportTemplateRowDetector::isMetaRow($data, 3));
    }

    public function test_does_not_skip_real_product_row(): void
    {
        $data = [
            'sku' => '80064102',
            'name_pl' => 'M-Tac Koszulka Odin Mystery Black',
            'name_en' => 'M-Tac Odin Mystery Black T-Shirt',
            'brand_code' => 'bottega-veneta',
            'primary_category_code' => 'men',
            'model_slug' => 'mystery-black',
        ];

        $this->assertFalse(ProductImportTemplateRowDetector::isMetaRow($data, 5));
    }
}
