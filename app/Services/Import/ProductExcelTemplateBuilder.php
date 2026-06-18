<?php

namespace App\Services\Import;

use App\Support\Import\ProductImportColumns;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExcelTemplateBuilder
{
    /**
     * @param  list<string>|null  $keys
     */
    public function download(?array $keys = null): StreamedResponse
    {
        $spreadsheet = $this->build($keys);
        $filename = 'product-import-template-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  list<string>|null  $keys
     */
    public function build(?array $keys = null): Spreadsheet
    {
        $keys = $this->normalizeKeys($keys);
        $spreadsheet = new Spreadsheet;
        $products = $spreadsheet->getActiveSheet();
        $products->setTitle('Товары');

        $labels = ProductImportColumns::labelsPl();
        $descriptions = ProductImportColumns::descriptions();

        foreach ($keys as $index => $key) {
            $column = $index + 1;
            $products->setCellValue([$column, 1], $key);
            $products->setCellValue([$column, 2], $labels[$key] ?? $key);
            $products->setCellValue([$column, 3], $descriptions[$key] ?? '');
        }

        $exampleDress = [
            'sku' => 'DRESS-001-BLACK',
            'name_pl' => 'Sukienka wieczorowa',
            'name_en' => 'Evening dress',
            'short_description_pl' => 'Elegancka sukienka na wieczór.',
            'status' => 'example',
            'type' => 'variable',
            'brand_code' => 'chanel',
            'primary_category_code' => 'women',
            'category_codes' => 'women',
            'catalog_codes' => 'main',
            'size_grid_code' => 'clothing_letter_women',
            'base_price' => '1290',
            'color_label' => 'Czarny',
            'color_hex' => '#1a1a1a',
            'is_new' => '1',
            'variant_sizes' => 's,m,l',
            'variant_prices' => '1290,1290,1390',
            'variant_stocks' => '5,3,2',
        ];

        $exampleBag = [
            'name_pl' => 'Torebka skórzana beżowa',
            'name_en' => 'Beige leather bag',
            'status' => 'example',
            'type' => 'simple',
            'primary_category_code' => 'accessories',
            'size_grid_code' => 'bags_sml',
            'base_price' => '2490',
            'variant_sizes' => 'm',
            'variant_prices' => '2490',
            'variant_stocks' => '3',
        ];

        foreach ($keys as $index => $key) {
            if (isset($exampleDress[$key])) {
                $products->setCellValue([$index + 1, 4], $exampleDress[$key]);
            }
            if (isset($exampleBag[$key])) {
                $products->setCellValue([$index + 1, 5], $exampleBag[$key]);
            }
        }

        $lastColumn = $this->columnLetter(count($keys));
        $products->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $products->getStyle("A1:{$lastColumn}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F4EA');
        $products->getStyle("A2:{$lastColumn}2")->getFont()->setBold(true);
        $products->getStyle("A3:{$lastColumn}3")->getFont()->setItalic(true);
        $products->freezePane('A4');

        $help = $spreadsheet->createSheet();
        $help->setTitle('Справка');
        $help->setCellValue('A1', 'Как заполнять импорт товаров');
        $help->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $lines = [
            '',
            'ОБЯЗАТЕЛЬНО: name_pl (название на польском).',
            'SKU — необязателен: если пустой, артикул создастся автоматически из названия.',
            'Несколько строк с одним SKU (или одним name_pl без SKU) = варианты размеров одного товара.',
            '',
            'Размеры — выберите ОДИН способ:',
            '  • variants = s:1290:5,m:1290:3  (размер:цена:остаток)',
            '  • variant_sizes + variant_prices + variant_stocks через запятую',
            '',
            'size_grid_code — кнопки на сайте (не таблица мерок!):',
            '  clothing_letter_women — одежда S/M/L',
            '  footwear_eu — обувь',
            '  bags_sml — сумки S/M/L',
            '  accessories_one_size — без размера',
            '',
            'model_slug — ТОЛЬКО для цветовых вариантов одной модели. Иначе пусто!',
            '',
            'Справочники (код или название, создаются автоматически): brand_code, category_codes, catalog_codes',
            '',
            'Статус: draft | published | archived.  Тип: simple | variable',
        ];

        foreach ($lines as $index => $line) {
            $help->setCellValue([1, $index + 2], $line);
        }

        $help->setCellValue('A20', 'Описание колонок в шаблоне');
        $help->getStyle('A20')->getFont()->setBold(true);
        $row = 21;

        foreach ($keys as $key) {
            $help->setCellValue([1, $row], ($labels[$key] ?? $key).': '.($descriptions[$key] ?? '—'));
            $row++;
        }

        $help->getColumnDimension('A')->setWidth(100);
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * @param  list<string>|null  $keys
     * @return list<string>
     */
    protected function normalizeKeys(?array $keys): array
    {
        $allowed = ProductImportColumns::keys();
        $selected = array_values(array_filter(
            $keys ?? ProductImportColumns::defaultTemplateKeys(),
            fn (string $key): bool => in_array($key, $allowed, true),
        ));

        if ($selected === []) {
            return ProductImportColumns::defaultTemplateKeys();
        }

        if (! in_array('name_pl', $selected, true)) {
            array_unshift($selected, 'name_pl');
        }

        return $selected;
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';
        $n = max(1, $index);

        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)).$letter;
            $n = intdiv($n, 26);
        }

        return $letter;
    }
}
