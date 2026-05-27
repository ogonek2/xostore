<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use App\Support\Shop\ShopLayoutData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected CheckoutService $checkout,
    ) {}

    public function show(string $locale): View|RedirectResponse
    {
        $cartData = $this->cart->present($locale);

        if (empty($cartData['items'])) {
            return redirect()->route('products.index', ['locale' => $locale]);
        }

        return view('shop.checkout', [
            ...ShopLayoutData::shared(),
            'cartCount' => $cartData['count'],
            'cart' => $cartData,
            'breadcrumbs' => [
                ['label' => __('shop.nav.shop'), 'url' => route('products.index', ['locale' => $locale])],
                ['label' => __('shop.checkout.title'), 'url' => null],
            ],
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:16'],
            'country' => ['required', 'string', 'size:2'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = $this->checkout->createOrder($data, $locale);

        return redirect()->route('checkout.thankyou', [
            'locale' => $locale,
            'order' => $order->access_token,
        ]);
    }

    public function thankyou(string $locale, string $order): View
    {
        $record = Order::query()
            ->with('items')
            ->where('access_token', $order)
            ->firstOrFail();

        return view('shop.checkout-thankyou', [
            ...ShopLayoutData::shared(),
            'cartCount' => 0,
            'order' => $record,
            'breadcrumbs' => [
                ['label' => __('shop.checkout.thankyou_title'), 'url' => null],
            ],
        ]);
    }
}
