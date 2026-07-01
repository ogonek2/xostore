<?php

namespace App\Services\Checkout;

use App\Enums\ShopEventType;
use App\Models\OrderStatus;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Services\Analytics\ShopAnalyticsService;
use App\Services\Cart\CartService;
use App\Support\Shop\OrderNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartService $cart,
        protected OrderShippingCalculator $shippingCalculator,
        protected PaymentRedirectBuilder $redirectBuilder,
    ) {}

    /**
     * @return array{order: Order, redirect_url: ?string, bank: ?array}
     */
    public function createOrder(array $data, string $locale): array
    {
        $cartData = $this->cart->present($locale);

        if (empty($cartData['items'])) {
            throw ValidationException::withMessages([
                'cart' => [__('shop.checkout.empty_cart')],
            ]);
        }

        $paymentMethod = PaymentMethod::query()
            ->where('is_active', true)
            ->whereKey($data['payment_method_id'])
            ->first();

        if (! $paymentMethod) {
            throw ValidationException::withMessages([
                'payment_method_id' => [__('shop.checkout.invalid_payment')],
            ]);
        }

        $subtotal = (float) $cartData['subtotal'];
        $shipping = $this->shippingCalculator->calculate($paymentMethod, $subtotal);

        $defaultStatus = OrderStatus::default();

        if (! $defaultStatus) {
            throw ValidationException::withMessages([
                'cart' => [__('shop.checkout.no_order_status')],
            ]);
        }

        $order = DB::transaction(function () use ($data, $locale, $cartData, $subtotal, $shipping, $paymentMethod, $defaultStatus) {
            $order = Order::query()->create([
                'number' => OrderNumberGenerator::generate(),
                'access_token' => (string) Str::uuid(),
                'order_status_id' => $defaultStatus->id,
                'payment_method_id' => $paymentMethod->id,
                'locale' => $locale,
                'currency' => config('shop.currency', 'PLN'),
                'email' => $data['email'],
                'phone' => $data['phone'],
                'customer_name' => $data['customer_name'],
                'city' => $data['city'],
                'delivery_address' => $data['delivery_address'],
                'country' => $data['country'] ?? 'PL',
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $subtotal + $shipping,
                'placed_at' => now(),
            ]);

            foreach ($cartData['items'] as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['name'],
                    'variant_sku' => $item['sku'],
                    'variant_label' => $item['variant_label'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['line_total'],
                ]);
            }

            $this->cart->clear();

            app(ShopAnalyticsService::class)->track(
                ShopEventType::OrderPlaced,
                payload: ['order_id' => $order->id, 'total' => (float) $order->total],
            );

            $order->loadMissing(['items', 'paymentMethod']);

            try {
                Mail::to($order->email)->send(new OrderPlacedMail($order));
            } catch (\Throwable) {
                // Не блокируем оформление при ошибке почты
            }

            try {
                app(\App\Support\Telegram\TelegramNotifier::class)->notifyOrder($order);
            } catch (\Throwable) {
                // Не блокируем оформление при ошибке Telegram
            }

            return $order;
        });

        $redirectUrl = $this->redirectBuilder->build($paymentMethod, $order);
        $bank = $paymentMethod->type->value === 'bank_transfer'
            ? $this->redirectBuilder->bankInstructions($paymentMethod, $order)
            : null;

        return [
            'order' => $order,
            'redirect_url' => $redirectUrl,
            'bank' => $bank,
        ];
    }

    /**
     * @return array{shipping: float, shipping_formatted: string, total: float, total_formatted: string, free: bool}
     */
    public function quote(int $paymentMethodId, float $subtotal, string $locale): array
    {
        $method = PaymentMethod::query()
            ->where('is_active', true)
            ->whereKey($paymentMethodId)
            ->firstOrFail();

        $shipping = $this->shippingCalculator->calculate($method, $subtotal);
        $total = $subtotal + $shipping;
        $symbol = config('shop.currency_symbol', 'zł');

        return [
            'shipping' => $shipping,
            'shipping_formatted' => $shipping > 0
                ? number_format($shipping, 0, ',', ' ').' '.$symbol
                : __('shop.checkout.free'),
            'total' => $total,
            'total_formatted' => number_format($total, 0, ',', ' ').' '.$symbol,
            'free' => $shipping <= 0,
        ];
    }
}
