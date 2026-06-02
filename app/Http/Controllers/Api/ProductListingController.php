<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductListingRequest;
use App\Models\Catalog;
use App\Models\Category;
use App\Support\Shop\ProductCardPresenter;
use App\Support\Shop\ProductListingFacets;
use App\Support\Shop\ProductListingQuery;
use Illuminate\Http\JsonResponse;

class ProductListingController extends Controller
{
    public function __invoke(ProductListingRequest $request, string $locale): JsonResponse
    {
        $category = $request->filled('category')
            ? Category::query()->where('is_active', true)->find($request->integer('category'))
            : null;

        $catalog = $request->filled('catalog')
            ? Catalog::query()->where('is_active', true)->find($request->integer('catalog'))
            : null;

        $listing = new ProductListingQuery(
            category: $category,
            catalog: $catalog,
            filters: $request->filters(),
        );

        $paginator = $listing->paginate($request->integer('page', 1), $request->perPage());

        return response()->json([
            'data' => ProductCardPresenter::collection($paginator->getCollection(), $locale)->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
                'facets' => ProductListingFacets::build($listing, $locale),
            ],
        ]);
    }
}
