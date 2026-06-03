<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Language;
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

        $this->preset(
            code: 'clothing_standard',
            unit: 'EU',
            plName: 'Odzież damska (XS–XXL)',
            enName: 'Women\'s clothing (XS–XXL)',
            sizes: [
                ['value' => 'xs', 'display' => 'XS'],
                ['value' => 's', 'display' => 'S'],
                ['value' => 'm', 'display' => 'M'],
                ['value' => 'l', 'display' => 'L'],
                ['value' => 'xl', 'display' => 'XL'],
                ['value' => 'xxl', 'display' => 'XXL'],
            ],
            categoryCodes: ['women', 'men'],
        );

        $this->preset(
            code: 'footwear_eu',
            unit: 'EU',
            plName: 'Obuwie (EU 36–41)',
            enName: 'Footwear (EU 36–41)',
            sizes: [
                ['value' => '36', 'display' => '36'],
                ['value' => '37', 'display' => '37'],
                ['value' => '38', 'display' => '38'],
                ['value' => '39', 'display' => '39'],
                ['value' => '40', 'display' => '40'],
                ['value' => '41', 'display' => '41'],
            ],
            categoryCodes: ['women-shoes', 'women', 'men'],
        );

        $this->preset(
            code: 'accessories_one_size',
            unit: null,
            plName: 'Akcesoria (universal)',
            enName: 'Accessories (one size)',
            sizes: [
                ['value' => 'one_size', 'display' => 'One size'],
            ],
            categoryCodes: ['accessories'],
        );
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
    ): void {
        $grid = SizeGrid::query()->updateOrCreate(
            ['code' => $code],
            ['unit' => $unit, 'is_active' => true],
        );

        $plLang = Language::query()->where('code', 'pl')->first();
        $enLang = Language::query()->where('code', 'en')->first();

        $grid->setTranslation('name', $plName, $plLang);
        $grid->setTranslation('name', $enName, $enLang);
        $grid->setTranslation(
            'description',
            'Rozmiary do wyboru na stronie produktu (przyciski S, M, L…). Tabelę mierki uzupełniasz osobno w karcie towaru.',
            $plLang,
        );
        $grid->setTranslation(
            'description',
            'Sizes for the product page selector (S, M, L buttons). Measurements table is filled separately on the product.',
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
