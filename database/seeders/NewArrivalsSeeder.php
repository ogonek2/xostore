<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class NewArrivalsSeeder extends Seeder
{
    public function run(): void
    {
        $pl = Language::query()->where('code', 'pl')->first();
        $en = Language::query()->where('code', 'en')->first();

        if (! $pl || ! $en) {
            return;
        }

        $women = Category::query()->where('code', 'women')->first();
        $accessories = Category::query()->where('code', 'accessories')->first();
        $men = Category::query()->where('code', 'men')->first();

        if (! $women) {
            return;
        }

        $gucci = Brand::query()->updateOrCreate(['code' => 'gucci'], ['is_active' => true]);
        $gucci->setTranslation('name', 'Gucci', $pl);
        $gucci->setTranslation('name', 'Gucci', $en);

        $acne = Brand::query()->updateOrCreate(['code' => 'acne'], ['is_active' => true]);
        $acne->setTranslation('name', 'Acne Studios', $pl);
        $acne->setTranslation('name', 'Acne Studios', $en);

        $productIds = [];

        $productIds[] = $this->product(
            sku: 'DEMO-GUCCI-ICON',
            brand: $gucci,
            category: $accessories ?? $women,
            pl: $pl,
            en: $en,
            translations: [
                'pl' => ['name' => 'Okulary Icon', 'slug' => 'gucci-icon'],
                'en' => ['name' => 'Icon Sunglasses', 'slug' => 'gucci-icon'],
            ],
            price: 1590,
            image: 'images/products/gucci-icon.jpg',
        )->id;

        $productIds[] = $this->product(
            sku: 'DEMO-ACNE-BOMBER',
            brand: $acne,
            category: $men ?? $women,
            pl: $pl,
            en: $en,
            translations: [
                'pl' => ['name' => 'Kurtka Bomber', 'slug' => 'acne-bomber'],
                'en' => ['name' => 'Bomber Jacket', 'slug' => 'acne-bomber'],
            ],
            price: 3990,
            image: 'images/products/acne-bomber.jpg',
        )->id;

        foreach (['DEMO-BV-ANDIAMO', 'DEMO-LV-TRAINER', 'DEMO-CHANEL-25P'] as $sku) {
            $existing = Product::query()->where('sku', $sku)->first();
            if ($existing) {
                $existing->update(['is_new' => true, 'published_at' => now()]);
                $productIds[] = $existing->id;
            }
        }

        $catalog = Catalog::query()->where('code', 'nowynki')->first();
        if ($catalog && $productIds !== []) {
            $sync = [];
            foreach (array_values(array_unique($productIds)) as $i => $id) {
                $sync[$id] = ['sort_order' => $i + 1];
            }
            $catalog->products()->sync($sync);
        }
    }

    protected function product(
        string $sku,
        Brand $brand,
        Category $category,
        Language $pl,
        Language $en,
        array $translations,
        float $price,
        string $image,
    ): Product {
        $product = Product::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'brand_id' => $brand->id,
                'primary_category_id' => $category->id,
                'status' => 'published',
                'type' => 'variable',
                'base_price' => $price,
                'is_featured' => false,
                'is_new' => true,
                'published_at' => now(),
            ]
        );

        foreach (['pl' => $pl, 'en' => $en] as $langCode => $language) {
            foreach ($translations[$langCode] as $field => $value) {
                $product->setTranslation($field, $value, $language);
            }
        }

        $product->categories()->sync([
            $category->id => ['is_primary' => true],
        ]);

        ProductImage::query()->updateOrCreate(
            ['product_id' => $product->id, 'path' => $image],
            ['is_primary' => true, 'sort_order' => 1, 'disk' => 'public']
        );

        ProductVariant::query()->updateOrCreate(
            ['sku' => $sku.'-DEFAULT'],
            [
                'product_id' => $product->id,
                'price' => $price,
                'stock_qty' => 8,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        return $product;
    }
}
