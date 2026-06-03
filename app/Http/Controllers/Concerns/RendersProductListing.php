<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\ProductListingRequest;
use App\Support\Seo\PageSeo;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\ProductCardPresenter;
use App\Support\Shop\ProductListingFacets;
use App\Support\Shop\ProductListingQuery;
use App\Support\Shop\ShopLayoutData;
use App\Models\Catalog;
use App\Models\Category;
use Illuminate\Contracts\View\View;

trait RendersProductListing
{
    protected function listingView(
        ProductListingRequest $request,
        string $locale,
        string $pageTitle,
        array $breadcrumbs,
        ?Category $category = null,
        ?Catalog $catalog = null,
        ?PageSeo $seo = null,
    ): View {
        $listing = new ProductListingQuery(
            category: $category,
            catalog: $catalog,
            filters: $request->filters(),
        );

        $paginator = $listing->paginate($request->integer('page', 1), $request->perPage());

        $filters = $request->filters();
        if ($request->filled('q')) {
            $filters['q'] = trim($request->input('q'));
        }

        $apiQuery = array_filter([
            'category' => $category?->id,
            'catalog' => $catalog?->id,
        ], fn ($value) => $value !== null && $value !== '');

        $subcategories = collect();
        if ($category) {
            $subcategories = $category->children->map(fn ($child) => [
                'label' => $child->translate('name', $locale),
                'url' => route('category.show', [
                    'locale' => $locale,
                    'category' => $child->translate('slug', $locale) ?? $child->code,
                ]),
            ]);
        }

        $seo ??= SeoBuilder::forListing(
            title: $pageTitle,
            canonical: url()->current(),
            fallbackDescription: __('seo.products_index_description', locale: $locale),
        );

        return view('shop.listing', [
            ...ShopLayoutData::shared(),
            'cartCount' => app(\App\Services\Cart\CartService::class)->count(),
            'seo' => $seo,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'category' => $category,
            'catalog' => $catalog,
            'facets' => ProductListingFacets::build($listing, $locale),
            'filters' => $filters,
            'products' => ProductCardPresenter::collection($paginator->getCollection(), $locale),
            'total' => $paginator->total(),
            'apiEndpoint' => route('api.products.index', ['locale' => $locale]),
            'apiQuery' => $apiQuery,
            'listingType' => $catalog ? 'catalog' : ($category ? 'category' : 'all'),
            'subcategories' => $subcategories,
        ]);
    }
}
