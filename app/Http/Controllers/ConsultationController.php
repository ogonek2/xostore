<?php

namespace App\Http\Controllers;

use App\Enums\ConsultationStatus;
use App\Models\ConsultationRequest;
use App\Models\Product;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\ShopLayoutData;
use App\Support\Shop\SlugResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function show(Request $request, string $locale, ?string $product = null): View
    {
        $productModel = null;
        $productContext = null;

        if ($product) {
            $productModel = SlugResolver::product($product, $locale);
            if ($productModel) {
                $productModel->load('brand.translates', 'translates');
                $productContext = [
                    'id' => $productModel->id,
                    'name' => $productModel->translate('name', $locale),
                    'slug' => $product,
                ];
            }
        }

        return view('shop.consultation', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::privatePage(__('shop.consultation.title')),
            'cartCount' => app(\App\Services\Cart\CartService::class)->count(),
            'product' => $productContext,
            'breadcrumbs' => [
                ['label' => __('shop.consultation.title'), 'url' => null],
            ],
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'message' => ['required', 'string', 'max:5000'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'preferred_at' => ['nullable', 'date'],
        ]);

        ConsultationRequest::query()->create([
            'status' => ConsultationStatus::New,
            'locale' => $locale,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'message' => $data['message'],
            'preferred_at' => $data['preferred_at'] ?? null,
        ]);

        return redirect()
            ->route('consultation.show', ['locale' => $locale])
            ->with('consultation_sent', true);
    }
}
