<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeGrid;
use App\Models\SizeGridValue;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ShopFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $pl = Language::query()->where('code', 'pl')->first();
        $en = Language::query()->where('code', 'en')->first();

        $women = $this->category('women', 'women', null, $pl, $en, [
            'pl' => ['name' => 'Damskie', 'slug' => 'damskie'],
            'en' => ['name' => 'Women', 'slug' => 'women'],
        ]);

        $this->category('women-shoes', 'women', $women, $pl, $en, [
            'pl' => ['name' => 'Buty', 'slug' => 'buty'],
            'en' => ['name' => 'Shoes', 'slug' => 'shoes'],
        ]);

        $men = $this->category('men', 'men', null, $pl, $en, [
            'pl' => ['name' => 'Męskie', 'slug' => 'meskie'],
            'en' => ['name' => 'Men', 'slug' => 'men'],
        ]);

        $this->category('accessories', 'accessories', null, $pl, $en, [
            'pl' => ['name' => 'Akcesoria', 'slug' => 'akcesoria'],
            'en' => ['name' => 'Accessories', 'slug' => 'accessories'],
        ]);

        $chanel = $this->brand('chanel', $pl, $en, [
            'pl' => ['name' => 'Chanel', 'slug' => 'chanel'],
            'en' => ['name' => 'Chanel', 'slug' => 'chanel'],
        ]);

        $this->tag('chanel', $pl, $en, [
            'pl' => ['name' => 'Chanel'],
            'en' => ['name' => 'Chanel'],
        ]);

        $colorGroup = AttributeGroup::query()->updateOrCreate(
            ['code' => 'color'],
            ['sort_order' => 1, 'is_filterable' => true]
        );
        $colorGroup->setTranslation('name', 'Kolor', $pl);
        $colorGroup->setTranslation('name', 'Color', $en);

        $colorAttr = Attribute::query()->updateOrCreate(
            ['attribute_group_id' => $colorGroup->id, 'code' => 'color'],
            ['type' => 'color_swatch', 'sort_order' => 1]
        );
        $colorAttr->setTranslation('name', 'Kolor', $pl);
        $colorAttr->setTranslation('name', 'Color', $en);

        $black = AttributeValue::query()->updateOrCreate(
            ['attribute_id' => $colorAttr->id, 'code' => 'black'],
            ['color_hex' => '#1a1a1a', 'sort_order' => 1]
        );
        $black->setTranslation('label', 'Czarny', $pl);
        $black->setTranslation('label', 'Black', $en);

        $euShoes = SizeGrid::query()->updateOrCreate(
            ['code' => 'eu_footwear'],
            ['unit' => 'EU', 'is_active' => true]
        );
        $euShoes->setTranslation('name', 'Rozmiary obuwia EU', $pl);
        $euShoes->setTranslation('name', 'EU footwear sizes', $en);

        foreach (['36', '37', '38', '39', '40'] as $i => $size) {
            SizeGridValue::query()->updateOrCreate(
                ['size_grid_id' => $euShoes->id, 'value' => $size],
                ['display_value' => $size, 'sort_order' => $i + 1]
            );
        }

        $women->sizeGrids()->syncWithoutDetaching([$euShoes->id]);

        $product = Product::query()->updateOrCreate(
            ['sku' => 'DEMO-CHANEL-25P'],
            [
                'brand_id' => $chanel->id,
                'primary_category_id' => $women->id,
                'status' => 'published',
                'type' => 'variable',
                'base_price' => 1190,
                'is_featured' => true,
                'published_at' => now(),
            ]
        );

        $product->setTranslation('name', 'Chanel 25P', $pl);
        $product->setTranslation('name', 'Chanel 25P', $en);
        $product->setTranslation('slug', 'chanel-25p', $pl);
        $product->setTranslation('slug', 'chanel-25p', $en);
        $product->setTranslation(
            'short_description',
            'Buty damskie — wersja demonstracyjna.',
            $pl
        );
        $product->setTranslation(
            'short_description',
            'Women\'s shoes — demo listing.',
            $en
        );

        $product->categories()->syncWithoutDetaching([
            $women->id => ['is_primary' => true],
        ]);

        $size38 = SizeGridValue::query()
            ->where('size_grid_id', $euShoes->id)
            ->where('value', '38')
            ->first();

        $variant = ProductVariant::query()->updateOrCreate(
            ['sku' => 'DEMO-CHANEL-25P-38-BLK'],
            [
                'product_id' => $product->id,
                'price' => 1190,
                'stock_qty' => 5,
                'size_grid_value_id' => $size38?->id,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $variant->attributeValues()->syncWithoutDetaching([$black->id]);
    }

    protected function category(
        string $code,
        string $type,
        ?Category $parent,
        Language $pl,
        Language $en,
        array $translations
    ): Category {
        $category = Category::query()->updateOrCreate(
            ['code' => $code],
            [
                'parent_id' => $parent?->id,
                'type' => $type,
                'depth' => $parent ? $parent->depth + 1 : 0,
                'path' => $parent ? "{$parent->path}/{$code}" : $code,
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 0,
            ]
        );

        foreach (['pl' => $pl, 'en' => $en] as $langCode => $language) {
            foreach ($translations[$langCode] as $field => $value) {
                $category->setTranslation($field, $value, $language);
            }
        }

        return $category;
    }

    protected function brand(string $code, Language $pl, Language $en, array $translations): Brand
    {
        $brand = Brand::query()->updateOrCreate(
            ['code' => $code],
            ['is_active' => true, 'sort_order' => 0]
        );

        foreach (['pl' => $pl, 'en' => $en] as $langCode => $language) {
            foreach ($translations[$langCode] as $field => $value) {
                $brand->setTranslation($field, $value, $language);
            }
        }

        return $brand;
    }

    protected function tag(string $code, Language $pl, Language $en, array $translations): Tag
    {
        $tag = Tag::query()->updateOrCreate(
            ['code' => $code],
            ['is_active' => true, 'sort_order' => 0]
        );

        foreach (['pl' => $pl, 'en' => $en] as $langCode => $language) {
            foreach ($translations[$langCode] as $field => $value) {
                $tag->setTranslation($field, $value, $language);
            }
        }

        return $tag;
    }
}
