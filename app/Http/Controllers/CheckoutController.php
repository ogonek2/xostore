<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use App\Services\Checkout\PaymentRedirectBuilder;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\ShopLayoutData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'delivery_method' => ['required', 'in:courier,paczkomat'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:16', 'regex:/^\d{2}-?\d{3}$/'],
            'street' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'country' => ['nullable', 'string', 'size:2'],
        ]);

        $postalCode = (string) $data['postal_code'];
        if (preg_match('/^\d{5}$/', $postalCode)) {
            $postalCode = substr($postalCode, 0, 2).'-'.substr($postalCode, 2);
        }
        $data['postal_code'] = $postalCode;
        $data['delivery_address'] = $data['street'];
        $data['_customer_ip'] = $request->ip();

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
            ->with(['items.variant.product', 'paymentMethod', 'latestPayment'])
            ->where('access_token', $order)
            ->firstOrFail();

        $bank = $record->paymentMethod
            ? $this->redirectBuilder->bankInstructions($record->paymentMethod, $record)
            : null;

        $purchaseEcommerce = $this->purchaseEcommercePayload($record);
        $shouldTrackPurchase = $this->shouldTrackPurchase($record);
        $awaitingPaidPurchase = $this->awaitsGatewayPayment($record);

        return view('shop.checkout-thankyou', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::privatePage(__('shop.checkout.thankyou_title')),
            'cartCount' => 0,
            'order' => $record,
            'bank' => $bank,
            'purchaseEcommerce' => $purchaseEcommerce,
            'shouldTrackPurchase' => $shouldTrackPurchase,
            'awaitingPaidPurchase' => $awaitingPaidPurchase,
            'paymentStatusUrl' => $awaitingPaidPurchase
                ? route('api.checkout.payment-status', ['locale' => $locale, 'order' => $record->access_token])
                : null,
            'breadcrumbs' => [
                ['label' => __('shop.checkout.thankyou_title'), 'url' => null],
            ],
        ]);
    }

    public function paymentStatus(string $locale, string $order): JsonResponse
    {
        $record = Order::query()
            ->with(['paymentMethod', 'latestPayment'])
            ->where('access_token', $order)
            ->firstOrFail();

        $payment = $record->latestPayment;
        $status = $payment?->status?->value ?? 'unknown';

        return response()->json([
            'status' => $status,
            'paid' => $this->shouldTrackPurchase($record),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function purchaseEcommercePayload(Order $order): array
    {
        $currency = $order->latestPayment?->currency
            ?: config('shop.currency', 'PLN');

        return [
            'transaction_id' => $order->number,
            'affiliation' => config('shop.analytics.affiliation'),
            'value' => round((float) $order->total, 2),
            'shipping' => round((float) $order->shipping, 2),
            'currency' => strtoupper((string) $currency),
            'items' => $order->items->map(fn ($item) => [
                'item_id' => (string) ($item->variant?->product_id ?? $item->variant_sku),
                'item_name' => $item->product_name,
                'item_variant' => $item->variant_label,
                'price' => round((float) $item->unit_price, 2),
                'quantity' => (int) $item->quantity,
            ])->values()->all(),
        ];
    }

    /**
     * Purchase must not fire on "place order" alone for PayU — only after Paid.
     * COD / bank transfer: order acceptance on thank-you is the conversion.
     */
    protected function shouldTrackPurchase(Order $order): bool
    {
        if ($this->isOnlinePaymentOrder($order)) {
            return $order->latestPayment?->status === PaymentStatus::Paid;
        }

        return true;
    }

    protected function awaitsGatewayPayment(Order $order): bool
    {
        if (! $this->isOnlinePaymentOrder($order)) {
            return false;
        }

        $payment = $order->latestPayment;

        return $payment !== null && $payment->status !== PaymentStatus::Paid;
    }

    protected function isOnlinePaymentOrder(Order $order): bool
    {
        if ($order->paymentMethod?->isGateway()) {
            return true;
        }

        $provider = strtolower((string) ($order->latestPayment?->provider ?? ''));

        return in_array($provider, ['payu', 'payment_gateway', 'gateway'], true);
    }

    public function retryPayment(Request $request, string $locale, string $order, string $payment): RedirectResponse
    {
        $record = Payment::query()
            ->where('public_token', $payment)
            ->whereHas('order', fn ($query) => $query->where('access_token', $order))
            ->firstOrFail();

        if (
            $record->status === PaymentStatus::Cancelled
            || ($record->status === PaymentStatus::Failed && $record->provider_order_id)
        ) {
            $record = Payment::query()->create([
                'id' => (string) Str::uuid(),
                'public_token' => Str::random(48),
                'order_id' => $record->order_id,
                'provider' => $record->provider,
                'amount_minor' => $record->amount_minor,
                'currency' => $record->currency,
                'status' => PaymentStatus::Pending,
                'idempotency_key' => (string) Str::uuid(),
            ]);
        }

        $redirectUrl = $this->checkout->initiatePayment($record, (string) $request->ip());

        if ($redirectUrl) {
            return redirect()->away($redirectUrl);
        }

        return redirect()
            ->route('checkout.thankyou', ['locale' => $locale, 'order' => $order])
            ->with('payment_retry_failed', true);
    }

    public function paymentReturn(string $locale, string $payment): RedirectResponse
    {
        $record = Payment::query()->with('order')->where('public_token', $payment)->firstOrFail();

        return redirect()->route('checkout.thankyou', [
            'locale' => $locale,
            'order' => $record->order->access_token,
        ]);
    }
}
