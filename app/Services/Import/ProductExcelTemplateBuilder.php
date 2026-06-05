<?php

namespace App\Services\Import;

use App\Support\Import\ProductImportColumns;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExcelTemplateBuilder
{
    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();
        $filename = 'product-import-template-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $products = $spreadsheet->getActiveSheet();
        $products->setTitle('Товары');

        $keys = ProductImportColumns::keys();
        $labels = ProductImportColumns::labelsPl();

        foreach ($keys as $index => $key) {
            $column = $index + 1;
            $products->setCellValue([$column, 1], $key);
            $products->setCellValue([$column, 2], $labels[$key] ?? $key);
        }

        $exampleProduct = [
            'sku' => 'DRESS-001-BLACK',
            'name_pl' => 'Sukienka wieczorowa',
            'name_en' => 'Evening dress',
            'slug_pl' => 'sukienka-wieczorowa',
            'short_description_pl' => 'Elegancka sukienka na wieczór.',
            'status' => 'draft',
            'type' => 'variable',
            'brand_code' => 'chanel',
            'primary_category_code' => 'women',
            'category_codes' => 'women',
            'catalog_codes' => 'main',
            'size_grid_code' => 'clothing_letter_women',
            'size_chart_preset_code' => 'women_dresses_cm',
            'base_price' => '1290',
            'model_slug' => 'evening-dress',
            'color_label' => 'Czarny',
            'color_hex' => '#1a1a1a',
            'is_new' => '1',
            'variant_sizes' => 's,m,l',
            'variant_prices' => '1290,1290,1390',
            'variant_stocks' => '5,3,2',
            'variant_defaults' => '0,1,0',
        ];

        foreach ($keys as $index => $key) {
            if (isset($exampleProduct[$key])) {
                $products->setCellValue([$index + 1, 3], $exampleProduct[$key]);
            }
        }

        $exampleCompact = [
            'sku' => 'SHIRT-002-WHITE',
            'name_pl' => 'Koszula biała',
            'category_codes' => 'women, accessories',
            'tag_codes' => 'chanel',
            'size_grid_code' => 'clothing_letter_women',
            'variants' => 's:990:4,m:990:6,l:1090:2',
        ];

        foreach ($keys as $index => $key) {
            if (isset($exampleCompact[$key])) {
                $products->setCellValue([$index + 1, 4], $exampleCompact[$key]);
            }
        }

        $products->getStyle('A1:AZ1')->getFont()->setBold(true);
        $products->getStyle('A1:AZ1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F4EA');
        $products->getStyle('A2:AZ2')->getFont()->setItalic(true);
        $products->freezePane('A3');

        $help = $spreadsheet->createSheet();
        $help->setTitle('Справка');
        $help->setCellValue('A1', 'Как заполнять импорт товаров');
        $help->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $lines = [
            '',
            'ОБЯЗАТЕЛЬНО: sku (артикул) и name_pl (название на польском).',
            'Все остальные поля — по желанию.',
            '',
            'Несколько строк с одним sku = варианты одного товара (размеры).',
            'Поля товара берутся из первой непустой строки группы.',
            '',
            'Коды справочников (должны существовать в админке):',
            '  brand_code — код бренда',
            '  primary_category_code / category_codes — коды категорий',
            '  catalog_codes, tag_codes, category_codes — через запятую',
            '  size_grid_code — пресет кнопок S/M/L',
            '  size_chart_preset_code — пресет таблицы мерок в см',
            '',
            'Варианты в ОДНОЙ ячейке (через запятую):',
            '  variant_sizes=s,m,l + variant_prices=1290,1290,1390 + variant_stocks=5,3,2',
            '  variants=s:1290:5,m:1290:3 (размер:цена:остаток)',
            '  variant_size=s,m,l — то же, что variant_sizes',
            '',
            'Или несколько строк с одним sku — по одному размеру в строке.',
            '',
            'Статус: draft | published | archived',
            'Тип: simple | variable',
            'Флаги is_*: 1/0, yes/no, tak',
            'Дата published_at: 2026-06-01 10:00:00',
            '',
            'После импорта проверьте товар в админке: фото, связи, таблицу мерок.',
        ];

        foreach ($lines as $index => $line) {
            $help->setCellValue([1, $index + 2], $line);
        }

        $help->getColumnDimension('A')->setWidth(90);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
