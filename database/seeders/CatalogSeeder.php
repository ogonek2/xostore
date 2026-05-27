<?php

namespace Database\Seeders;

use App\Enums\CatalogType;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $pl = Language::query()->where('code', 'pl')->first();
        $en = Language::query()->where('code', 'en')->first();

        if (! $pl || ! $en) {
            return;
        }

        $trendy = $this->catalog('trendy', CatalogType::Mixed, $pl, $en, [
            'pl' => ['name' => 'Trendy', 'slug' => 'trendy'],
            'en' => ['name' => 'Trends', 'slug' => 'trends'],
        ], showOnHome: true, sort: 1);

        $promo = $this->catalog('promotions', CatalogType::Manual, $pl, $en, [
            'pl' => ['name' => 'Promocje', 'slug' => 'promocje'],
            'en' => ['name' => 'Promotions', 'slug' => 'promotions'],
        ], sort: 2);

        $this->catalog('nowynki', CatalogType::Manual, $pl, $en, [
            'pl' => ['name' => 'Nowynki', 'slug' => 'nowynki'],
            'en' => ['name' => 'New in', 'slug' => 'new-in'],
        ], showOnHome: true, sort: 3);

        $this->catalog('ready_to_ship', CatalogType::Manual, $pl, $en, [
            'pl' => ['name' => 'Towary w magazynie', 'slug' => 'w-magazynie'],
            'en' => ['name' => 'In stock now', 'slug' => 'in-stock'],
        ], sort: 4);

        $women = Category::query()->where('code', 'women')->first();
        $men = Category::query()->where('code', 'men')->first();

        if ($women) {
            $trendy->categories()->syncWithoutDetaching([$women->id]);
        }
        if ($men) {
            $trendy->categories()->syncWithoutDetaching([$men->id]);
        }

        $product = Product::query()->first();
        if ($product) {
            $promo->products()->syncWithoutDetaching([
                $product->id => ['sort_order' => 1],
            ]);
        }
    }

    protected function catalog(
        string $code,
        CatalogType $type,
        Language $pl,
        Language $en,
        array $translations,
        bool $showOnHome = false,
        int $sort = 0
    ): Catalog {
        $catalog = Catalog::query()->updateOrCreate(
            ['code' => $code],
            [
                'type' => $type,
                'is_active' => true,
                'show_on_homepage' => $showOnHome,
                'sort_order' => $sort,
                'published_at' => now(),
            ]
        );

        foreach (['pl' => $pl, 'en' => $en] as $langCode => $language) {
            foreach ($translations[$langCode] as $field => $value) {
                $catalog->setTranslation($field, $value, $language);
            }
        }

        return $catalog;
    }
}
