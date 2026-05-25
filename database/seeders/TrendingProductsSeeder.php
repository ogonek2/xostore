<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class TrendingProductsSeeder extends Seeder
{
    public function run(): void
    {
        $pl = Language::query()->where('code', 'pl')->first();
        $en = Language::query()->where('code', 'en')->first();

        if (! $pl || ! $en) {
            return;
        }

        $women = Category::query()->where('code', 'women')->first();
        $shoes = Category::query()->where('code', 'women-shoes')->first();

        $colorAttr = Attribute::query()->where('code', 'color')->first();
        if (! $colorAttr) {
            return;
        }

        $colors = $this->ensureColors($colorAttr, $pl, $en);

        $bottega = Brand::query()->updateOrCreate(['code' => 'bottega'], ['is_active' => true]);
        $bottega->setTranslation('name', 'Bottega Veneta', $pl);
        $bottega->setTranslation('name', 'Bottega Veneta', $en);

        $lv = Brand::query()->updateOrCreate(['code' => 'louis-vuitton'], ['is_active' => true]);
        $lv->setTranslation('name', 'Louis Vuitton', $pl);
        $lv->setTranslation('name', 'Louis Vuitton', $en);

        $chanel = Brand::query()->where('code', 'chanel')->first();

        $this->product(
            sku: 'DEMO-BV-ANDIAMO',
            brand: $bottega,
            category: $women,
            extraCategories: [$women],
            pl: $pl,
            en: $en,
            translations: [
                'pl' => ['name' => 'Andiamo', 'slug' => 'bottega-veneta-andiamo'],
                'en' => ['name' => 'Andiamo', 'slug' => 'bottega-veneta-andiamo'],
            ],
            price: 1850,
            image: 'images/products/bottega-andiamo.jpg',
            colorCodes: ['white', 'beige', 'black'],
            colors: $colors,
        );

        $this->product(
            sku: 'DEMO-LV-TRAINER',
            brand: $lv,
            category: $shoes ?? $women,
            extraCategories: array_filter([$shoes, $women]),
            pl: $pl,
            en: $en,
            translations: [
                'pl' => ['name' => 'Trainer', 'slug' => 'louis-vuitton-trainer'],
                'en' => ['name' => 'Trainer', 'slug' => 'louis-vuitton-trainer'],
            ],
            price: 1850,
            image: 'images/products/lv-trainer.jpg',
            colorCodes: ['white', 'brown', 'black', 'pink', 'turquoise'],
            colors: $colors,
        );

        if ($chanel) {
            Product::query()->where('sku', 'DEMO-CHANEL-25P')->update([
                'is_featured' => true,
                'base_price' => 1190,
            ]);

            ProductImage::query()->updateOrCreate(
                ['product_id' => Product::query()->where('sku', 'DEMO-CHANEL-25P')->value('id'), 'path' => 'images/products/chanel-25p.jpg'],
                ['is_primary' => true, 'sort_order' => 1, 'disk' => 'public']
            );
        }
    }

    protected function ensureColors(Attribute $colorAttr, Language $pl, Language $en): array
    {
        $map = [
            'white' => ['#ffffff', 'Biały', 'White'],
            'beige' => ['#d4c4b0', 'Beż', 'Beige'],
            'black' => ['#1a1a1a', 'Czarny', 'Black'],
            'brown' => ['#6b4f3a', 'Brąz', 'Brown'],
            'pink' => ['#e8b4b8', 'Róż', 'Pink'],
            'turquoise' => ['#5ec6ca', 'Turkus', 'Turquoise'],
        ];

        $result = [];

        foreach ($map as $code => [$hex, $plLabel, $enLabel]) {
            $value = AttributeValue::query()->updateOrCreate(
                ['attribute_id' => $colorAttr->id, 'code' => $code],
                ['color_hex' => $hex, 'sort_order' => count($result) + 1]
            );
            $value->setTranslation('label', $plLabel, $pl);
            $value->setTranslation('label', $enLabel, $en);
            $result[$code] = $value;
        }

        return $result;
    }

    protected function product(
        string $sku,
        Brand $brand,
        Category $category,
        array $extraCategories,
        Language $pl,
        Language $en,
        array $translations,
        float $price,
        string $image,
        array $colorCodes,
        array $colors,
    ): void {
        $product = Product::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'brand_id' => $brand->id,
                'primary_category_id' => $category->id,
                'status' => 'published',
                'type' => 'variable',
                'base_price' => $price,
                'is_featured' => true,
                'published_at' => now(),
            ]
        );

        foreach (['pl' => $pl, 'en' => $en] as $langCode => $language) {
            foreach ($translations[$langCode] as $field => $value) {
                $product->setTranslation($field, $value, $language);
            }
        }

        $sync = [];
        foreach (array_filter($extraCategories) as $cat) {
            $sync[$cat->id] = ['is_primary' => $cat->id === $category->id];
        }
        $product->categories()->sync($sync);

        ProductImage::query()->updateOrCreate(
            ['product_id' => $product->id, 'path' => $image],
            ['is_primary' => true, 'sort_order' => 1, 'disk' => 'public']
        );

        $variant = ProductVariant::query()->updateOrCreate(
            ['sku' => $sku.'-DEFAULT'],
            [
                'product_id' => $product->id,
                'price' => $price,
                'stock_qty' => 10,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $colorIds = collect($colorCodes)
            ->map(fn ($code) => $colors[$code]->id ?? null)
            ->filter()
            ->all();

        $variant->attributeValues()->sync($colorIds);
    }
}
