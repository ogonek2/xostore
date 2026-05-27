<?php

namespace App\Http\Controllers;

use App\Enums\ShopEventType;
use App\Services\Analytics\ShopAnalyticsService;
use App\Services\Cart\CartService;
use App\Support\Shop\ProductDetailPresenter;
use App\Support\Shop\ShopLayoutData;
use App\Support\Shop\SlugResolver;
use Illuminate\View\View;

class ProductShowController extends Controller
{
    public function __invoke(string $locale, string $product): View
    {
        $record = SlugResolver::product($product, $locale);

        if (! $record) {
            abort(404);
        }

        $presented = ProductDetailPresenter::fromProduct(
            $record,
            $locale,
            request('color')
        );

        app(ShopAnalyticsService::class)->track(
            ShopEventType::ProductView,
            $record->id,
        );

        $breadcrumbs = [
            ['label' => __('shop.nav.shop'), 'url' => route('products.index', ['locale' => $locale])],
        ];

        if ($presented['category'] && $presented['category_slug']) {
            $breadcrumbs[] = [
                'label' => $presented['category'],
                'url' => route('category.show', ['locale' => $locale, 'category' => $presented['category_slug']]),
            ];
        }

        $breadcrumbs[] = ['label' => $presented['name'], 'url' => null];

        return view('shop.product', [
            ...ShopLayoutData::shared(),
            'cartCount' => app(CartService::class)->count(),
            'product' => $presented,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
