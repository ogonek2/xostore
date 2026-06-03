<?php

namespace App\Http\Controllers;

use App\Enums\ShopEventType;
use App\Services\Analytics\ShopAnalyticsService;
use App\Services\Cart\CartService;
use App\Support\Seo\SeoBuilder;
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

        $cartService = app(CartService::class);
        $cart = $cartService->present($locale);
        $cartLines = collect($cart['items'])
            ->where('product_id', $record->id)
            ->map(fn (array $item) => [
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'variant_label' => $item['variant_label'],
            ])
            ->values()
            ->all();

        $presented['cart'] = [
            'in_cart' => $cartLines !== [],
            'lines' => $cartLines,
        ];

        return view('shop.product', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::forProduct($presented, $locale),
            'cartCount' => $cartService->count(),
            'product' => $presented,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
