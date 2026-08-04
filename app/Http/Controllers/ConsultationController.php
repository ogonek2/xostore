<?php

namespace App\Http\Controllers;

use App\Enums\ConsultationStatus;
use App\Http\Requests\StoreConsultationRequest;
use App\Models\ConsultationRequest;
use App\Services\Security\TurnstileVerifier;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\ShopLayoutData;
use App\Support\Shop\SlugResolver;
use App\Support\Telegram\TelegramNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $formToken = Str::random(40);
        $request->session()->put([
            'consultation.form_token' => $formToken,
            'consultation.form_issued_at' => now()->timestamp,
        ]);

        return view('shop.consultation', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::privatePage(__('shop.consultation.title')),
            'cartCount' => app(\App\Services\Cart\CartService::class)->count(),
            'product' => $productContext,
            'formToken' => $formToken,
            'turnstileSiteKey' => app(TurnstileVerifier::class)->isConfigured()
                ? config('services.turnstile.site_key')
                : null,
            'breadcrumbs' => [
                ['label' => __('shop.consultation.title'), 'url' => null],
            ],
        ]);
    }

    public function store(StoreConsultationRequest $request, string $locale): RedirectResponse
    {
        $data = $request->validated();
        $source = $request->submissionSource();

        $consultation = ConsultationRequest::query()->create([
            'status' => ConsultationStatus::New,
            'locale' => $locale,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'message' => $data['message'],
            'preferred_at' => $data['preferred_at'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512) ?: null,
            'referer' => substr((string) $request->headers->get('referer'), 0, 512) ?: null,
            'submission_source' => $source,
        ]);

        $request->session()->forget([
            'consultation.form_token',
            'consultation.form_issued_at',
        ]);

        try {
            app(TelegramNotifier::class)->notifyConsultation($consultation);
        } catch (\Throwable) {
            // Не блокируем отправку формы при ошибке Telegram
        }

        return redirect()
            ->route('consultation.show', ['locale' => $locale])
            ->with('consultation_sent', true);
    }
}
