<?php

namespace Database\Seeders;

use App\Enums\PromotionLayout;
use App\Enums\PromotionProductTargetType;
use App\Models\Category;
use App\Models\Language;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
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
        $accessories = Category::query()->where('code', 'accessories')->first();

        $expires = now()->endOfMonth();

        $this->promotion(
            code: 'jackets-30',
            layout: PromotionLayout::Featured,
            category: $women,
            discount: 30,
            sort: 1,
            image: 'images/promotions/jackets.jpg',
            expires: $expires,
            pl: $pl,
            en: $en,
            translations: [
                'pl' => [
                    'title' => '−30% na wszystkie kurtki',
                    'subtitle' => 'Tylko do '.$expires->locale('pl')->translatedFormat('j F'),
                    'cta_label' => 'Zobacz',
                ],
                'en' => [
                    'title' => '−30% off all jackets',
                    'subtitle' => 'Until '.$expires->format('M j'),
                    'cta_label' => 'Shop now',
                ],
            ],
        );

        $this->promotion(
            code: 'shoes-20',
            layout: PromotionLayout::Compact,
            category: $shoes,
            discount: 20,
            sort: 2,
            image: 'images/promotions/shoes.jpg',
            expires: $expires,
            pl: $pl,
            en: $en,
            translations: [
                'pl' => [
                    'title' => '−20% na obuwie',
                    'subtitle' => null,
                    'cta_label' => 'Zobacz',
                ],
                'en' => [
                    'title' => '−20% off footwear',
                    'subtitle' => null,
                    'cta_label' => 'Shop now',
                ],
            ],
        );

        $this->promotion(
            code: 'bags-15',
            layout: PromotionLayout::Compact,
            category: $accessories,
            discount: 15,
            sort: 3,
            image: 'images/promotions/bags.jpg',
            expires: $expires,
            pl: $pl,
            en: $en,
            translations: [
                'pl' => [
                    'title' => '−15% na torebki',
                    'subtitle' => null,
                    'cta_label' => 'Zobacz',
                ],
                'en' => [
                    'title' => '−15% off bags',
                    'subtitle' => null,
                    'cta_label' => 'Shop now',
                ],
            ],
        );
    }

    protected function promotion(
        string $code,
        PromotionLayout $layout,
        ?Category $category,
        int $discount,
        int $sort,
        string $image,
        $expires,
        Language $pl,
        Language $en,
        array $translations,
    ): void {
        $promotion = Promotion::query()->updateOrCreate(
            ['code' => $code],
            [
                'layout' => $layout,
                'product_target_type' => $category
                    ? PromotionProductTargetType::Category
                    : null,
                'category_id' => $category?->id,
                'discount_percent' => $discount,
                'image_path' => $image,
                'expires_at' => $expires,
                'is_active' => true,
                'show_on_homepage' => true,
                'sort_order' => $sort,
            ]
        );

        foreach (['pl' => $pl, 'en' => $en] as $langCode => $language) {
            foreach ($translations[$langCode] as $field => $value) {
                if ($value !== null && $value !== '') {
                    $promotion->setTranslation($field, $value, $language);
                }
            }
        }
    }
}
