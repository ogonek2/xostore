<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\SizeGrid;
use App\Models\SizeGridValue;
use Illuminate\Database\Seeder;

class SizeGridPresetsSeeder extends Seeder
{
    public function run(): void
    {
        $pl = Language::query()->where('code', 'pl')->first();
        $en = Language::query()->where('code', 'en')->first();

        if (! $pl || ! $en) {
            return;
        }

        $letter = static fn (array $codes): array => array_map(
            fn (string $code) => [
                'value' => strtolower($code),
                'display' => strtoupper($code),
            ],
            $codes,
        );

        $numeric = static fn (int $from, int $to, int $step = 1): array => array_map(
            fn (int $n) => ['value' => (string) $n, 'display' => (string) $n],
            range($from, $to, $step),
        );

        $this->preset(
            code: 'clothing_letter_women',
            unit: null,
            plName: 'Odzież damska (XXS–XXL)',
            enName: 'Women\'s clothing (XXS–XXL)',
            sizes: $letter(['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL']),
            categoryCodes: ['women'],
            presetKind: 'clothing_letters',
        );

        $this->preset(
            code: 'clothing_letter_men',
            unit: null,
            plName: 'Odzież męska (S–3XL)',
            enName: 'Men\'s clothing (S–3XL)',
            sizes: $letter(['S', 'M', 'L', 'XL', 'XXL', '3XL']),
            categoryCodes: ['men'],
        );

        $this->preset(
            code: 'clothing_letter_unisex',
            unit: null,
            plName: 'Odzież unisex (XS–XXL)',
            enName: 'Unisex clothing (XS–XXL)',
            sizes: $letter(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            categoryCodes: ['women', 'men'],
        );

        $this->preset(
            code: 'clothing_eu_numeric',
            unit: 'EU',
            plName: 'Odzież numeryczna EU (32–48)',
            enName: 'EU numeric clothing (32–48)',
            sizes: $numeric(32, 48, 2),
            categoryCodes: ['women', 'men'],
        );

        $this->preset(
            code: 'denim_waist',
            unit: 'EU',
            plName: 'Jeansy / spodnie (talia 24–38)',
            enName: 'Denim / trousers (waist 24–38)',
            sizes: $numeric(24, 38, 2),
            categoryCodes: ['women', 'men'],
        );

        $this->preset(
            code: 'outerwear_letter',
            unit: null,
            plName: 'Kurtki i płaszcze (XS–XXL)',
            enName: 'Outerwear (XS–XXL)',
            sizes: $letter(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            categoryCodes: ['women', 'men'],
        );

        $this->preset(
            code: 'knitwear_letter',
            unit: null,
            plName: 'Swetry i dzianina (XS–XL)',
            enName: 'Knitwear (XS–XL)',
            sizes: $letter(['XS', 'S', 'M', 'L', 'XL']),
            categoryCodes: ['women', 'men'],
        );

        $this->preset(
            code: 'lingerie_letter',
            unit: null,
            plName: 'Bielizna (XS–L)',
            enName: 'Lingerie (XS–L)',
            sizes: $letter(['XS', 'S', 'M', 'L']),
            categoryCodes: ['women'],
        );

        $this->preset(
            code: 'footwear_eu',
            unit: 'EU',
            plName: 'Obuwie damskie/męskie (EU 35–42)',
            enName: 'Footwear (EU 35–42)',
            sizes: $numeric(35, 42),
            categoryCodes: ['women-shoes', 'women', 'men'],
            presetKind: 'footwear',
        );

        $this->preset(
            code: 'accessories_one_size',
            unit: null,
            plName: 'Akcesoria (one size)',
            enName: 'Accessories (one size)',
            sizes: [
                ['value' => 'one_size', 'display' => 'One size'],
            ],
            categoryCodes: ['accessories'],
            presetKind: 'one_size',
        );

        $this->preset(
            code: 'bags_sml',
            unit: null,
            plName: 'Torebki S / M / L',
            enName: 'Bags S / M / L',
            sizes: $letter(['S', 'M', 'L']),
            categoryCodes: ['accessories', 'women'],
            presetKind: 'bags',
        );

        $this->preset(
            code: 'bags_cm',
            unit: 'cm',
            plName: 'Torebki (szerokość cm 25–35)',
            enName: 'Bags (width cm 25–35)',
            sizes: $numeric(25, 35, 5),
            categoryCodes: ['accessories', 'women'],
            presetKind: 'bags',
        );

        $this->preset(
            code: 'belts_cm',
            unit: 'cm',
            plName: 'Paski (cm 80–110)',
            enName: 'Belts (cm 80–110)',
            sizes: $numeric(80, 110, 5),
            categoryCodes: ['accessories', 'women', 'men'],
        );

        $this->migrateLegacyPreset('eu_footwear', 'footwear_eu');
        $this->migrateLegacyPreset('clothing_standard', 'clothing_letter_unisex');
    }

    protected function migrateLegacyPreset(string $legacyCode, string $modernCode): void
    {
        $legacy = SizeGrid::query()->where('code', $legacyCode)->first();
        $modern = SizeGrid::query()->where('code', $modernCode)->first();

        if (! $legacy) {
            return;
        }

        if ($modern) {
            Product::query()
                ->where('size_grid_id', $legacy->id)
                ->update(['size_grid_id' => $modern->id]);
        }

        $legacy->update(['is_active' => false]);
    }

    /**
     * @param  list<array{value: string, display: string}>  $sizes
     * @param  list<string>  $categoryCodes
     */
    protected function preset(
        string $code,
        ?string $unit,
        string $plName,
        string $enName,
        array $sizes,
        array $categoryCodes = [],
        ?string $presetKind = null,
    ): void {
        $grid = SizeGrid::query()->updateOrCreate(
            ['code' => $code],
            ['unit' => $unit, 'is_active' => true, 'preset_kind' => $presetKind],
        );

        $plLang = Language::query()->where('code', 'pl')->first();
        $enLang = Language::query()->where('code', 'en')->first();

        $grid->setTranslation('name', $plName, $plLang);
        $grid->setTranslation('name', $enName, $enLang);
        $grid->setTranslation(
            'description',
            'Przyciski rozmiaru na stronie produktu (S, M, L, 38…). Tabelę mierki (klatka, talia) uzupełniasz osobno w towarze.',
            $plLang,
        );
        $grid->setTranslation(
            'description',
            'Size buttons on the product page (S, M, L, 38…). Fill the measurement table (chest, waist) separately on the product.',
            $enLang,
        );

        foreach ($sizes as $index => $size) {
            SizeGridValue::query()->updateOrCreate(
                [
                    'size_grid_id' => $grid->id,
                    'value' => $size['value'],
                ],
                [
                    'display_value' => $size['display'],
                    'sort_order' => $index + 1,
                ],
            );
        }

        $categoryIds = Category::query()
            ->whereIn('code', $categoryCodes)
            ->pluck('id');

        if ($categoryIds->isNotEmpty()) {
            $grid->categories()->syncWithoutDetaching($categoryIds->all());
        }
    }
}
