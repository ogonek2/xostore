<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\SizeChartPreset;
use App\Models\SizeChartPresetRow;
use Illuminate\Database\Seeder;

class SizeChartPresetSeeder extends Seeder
{
    public function run(): void
    {
        $pl = Language::query()->where('code', 'pl')->first();
        $en = Language::query()->where('code', 'en')->first();

        if (! $pl || ! $en) {
            return;
        }

        $this->preset(
            code: 'women_tops_cm',
            plName: 'Bluzki / topy damskie (cm)',
            enName: 'Women\'s tops (cm)',
            rows: [
                ['size' => 'XS', 'chest' => 80, 'waist' => 62, 'hips' => 86],
                ['size' => 'S', 'chest' => 84, 'waist' => 66, 'hips' => 90],
                ['size' => 'M', 'chest' => 88, 'waist' => 70, 'hips' => 94],
                ['size' => 'L', 'chest' => 92, 'waist' => 74, 'hips' => 98],
                ['size' => 'XL', 'chest' => 96, 'waist' => 78, 'hips' => 102],
                ['size' => 'XXL', 'chest' => 100, 'waist' => 82, 'hips' => 106],
            ],
        );

        $this->preset(
            code: 'women_dresses_cm',
            plName: 'Sukienki damskie (cm)',
            enName: 'Women\'s dresses (cm)',
            rows: [
                ['size' => 'XS', 'chest' => 78, 'waist' => 60, 'hips' => 84],
                ['size' => 'S', 'chest' => 82, 'waist' => 64, 'hips' => 88],
                ['size' => 'M', 'chest' => 86, 'waist' => 68, 'hips' => 92],
                ['size' => 'L', 'chest' => 90, 'waist' => 72, 'hips' => 96],
                ['size' => 'XL', 'chest' => 94, 'waist' => 76, 'hips' => 100],
            ],
        );

        $this->preset(
            code: 'men_tops_cm',
            plName: 'Koszule / bluzy męskie (cm)',
            enName: 'Men\'s tops (cm)',
            rows: [
                ['size' => 'S', 'chest' => 92, 'waist' => 78, 'hips' => 94],
                ['size' => 'M', 'chest' => 96, 'waist' => 82, 'hips' => 98],
                ['size' => 'L', 'chest' => 100, 'waist' => 86, 'hips' => 102],
                ['size' => 'XL', 'chest' => 104, 'waist' => 90, 'hips' => 106],
                ['size' => 'XXL', 'chest' => 108, 'waist' => 94, 'hips' => 110],
            ],
        );

        $this->preset(
            code: 'men_trousers_cm',
            plName: 'Spodnie męskie (cm)',
            enName: 'Men\'s trousers (cm)',
            rows: [
                ['size' => '46', 'chest' => null, 'waist' => 78, 'hips' => 96, 'inseam' => 80],
                ['size' => '48', 'chest' => null, 'waist' => 82, 'hips' => 100, 'inseam' => 80],
                ['size' => '50', 'chest' => null, 'waist' => 86, 'hips' => 104, 'inseam' => 82],
                ['size' => '52', 'chest' => null, 'waist' => 90, 'hips' => 108, 'inseam' => 82],
            ],
        );

        $this->preset(
            code: 'outerwear_cm',
            plName: 'Kurtki / płaszcze (cm)',
            enName: 'Outerwear (cm)',
            rows: [
                ['size' => 'S', 'chest' => 96, 'waist' => 82, 'hips' => 98],
                ['size' => 'M', 'chest' => 100, 'waist' => 86, 'hips' => 102],
                ['size' => 'L', 'chest' => 104, 'waist' => 90, 'hips' => 106],
                ['size' => 'XL', 'chest' => 108, 'waist' => 94, 'hips' => 110],
            ],
        );
    }

    /**
     * @param  list<array{size: string, chest?: float|null, waist?: float|null, hips?: float|null, inseam?: float|null}>  $rows
     */
    protected function preset(string $code, string $plName, string $enName, array $rows): void
    {
        $preset = SizeChartPreset::query()->updateOrCreate(
            ['code' => $code],
            ['unit' => 'cm', 'is_active' => true],
        );

        $plLang = Language::query()->where('code', 'pl')->first();
        $enLang = Language::query()->where('code', 'en')->first();

        $preset->setTranslation('name', $plName, $plLang);
        $preset->setTranslation('name', $enName, $enLang);
        $preset->setTranslation(
            'description',
            'Wizualna tabela rozmiarów na stronie produktu — wartości w centymetrach.',
            $plLang,
        );
        $preset->setTranslation(
            'description',
            'Visual size chart on the product page — measurements in centimetres.',
            $enLang,
        );

        foreach ($rows as $index => $row) {
            SizeChartPresetRow::query()->updateOrCreate(
                [
                    'size_chart_preset_id' => $preset->id,
                    'size' => $row['size'],
                ],
                [
                    'chest_cm' => $row['chest'] ?? null,
                    'waist_cm' => $row['waist'] ?? null,
                    'hips_cm' => $row['hips'] ?? null,
                    'inseam_cm' => $row['inseam'] ?? null,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
