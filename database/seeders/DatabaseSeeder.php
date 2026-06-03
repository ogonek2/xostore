<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            NavMenuSeeder::class,
            NewsletterGroupSeeder::class,
            PaymentMethodSeeder::class,
            AdminUserSeeder::class,
            SizeGridPresetsSeeder::class,
            SizeChartPresetSeeder::class,
            ShopFoundationSeeder::class,
            CatalogSeeder::class,
            TrendingProductsSeeder::class,
            PromotionSeeder::class,
            NewArrivalsSeeder::class,
        ]);
    }
}
