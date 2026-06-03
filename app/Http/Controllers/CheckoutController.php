<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use App\Services\Checkout\PaymentRedirectBuilder;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\ShopLayoutData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected CheckoutService $checkout,
        protected PaymentRedirectBuilder $redirectBuilder,
    ) {}

    public function show(string $locale): View|RedirectResponse
    {
        $cartData = $this->cart->present($locale);

        if (empty($cartData['items'])) {
            return redirect()->route('products.index', ['locale' => $locale]);
        }

        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PaymentMethod $method) => [
                'id' => $method->id,
                'code' => $method->code,
                'type' => $method->type->value,
                'label' => $method->label($locale),
                'instructions' => $method->instructionsText($locale),
            ]);

        return view('shop.checkout', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::privatePage(__('shop.checkout.title')),
            'cartCount' => $cartData['count'],
            'cart' => $cartData,
            'paymentMethods' => $paymentMethods,
            'quoteUrl' => route('api.checkout.quote', ['locale' => $locale]),
            'breadcrumbs' => [
                ['label' => __('shop.nav.shop'), 'url' => route('products.index', ['locale' => $locale])],
                ['label' => __('shop.checkout.title'), 'url' => null],
            ],
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:120'],
            'delivery_address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'country' => ['nullable', 'string', 'size:2'],
        ]);

        $result = $this->checkout->createOrder($data, $locale);
        $order = $result['order'];

        if ($result['redirect_url']) {
            session([
                'checkout.pending_order' => $order->access_token,
            ]);

            return redirect()->away($result['redirect_url']);
        }

        return redirect()->route('checkout.thankyou', [
            'locale' => $locale,
            'order' => $order->access_token,
        ]);
    }

    public function thankyou(string $locale, string $order): View
    {
        $record = Order::query()
            ->with(['items', 'paymentMethod'])
            ->where('access_token', $order)
            ->firstOrFail();

        $bank = $record->paymentMethod
            ? $this->redirectBuilder->bankInstructions($record->paymentMethod, $record)
            : null;

        return view('shop.checkout-thankyou', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::privatePage(__('shop.checkout.thankyou_title')),
            'cartCount' => 0,
            'order' => $record,
            'bank' => $bank,
            'breadcrumbs' => [
                ['label' => __('shop.checkout.thankyou_title'), 'url' => null],
            ],
        ]);
    }
}
