<?php

namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\ShopEventType;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Analytics\ShopAnalyticsService;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartService $cart,
    ) {}

    public function createOrder(array $data, string $locale): Order
    {
        $cartData = $this->cart->present($locale);

        if (empty($cartData['items'])) {
            throw ValidationException::withMessages([
                'cart' => [__('shop.checkout.empty_cart')],
            ]);
        }

        $shipping = (float) config('shop.checkout.shipping_cost', 0);
        $freeFrom = (float) config('shop.checkout.free_shipping_from', 0);
        $subtotal = (float) $cartData['subtotal'];

        if ($freeFrom > 0 && $subtotal >= $freeFrom) {
            $shipping = 0;
        }

        return DB::transaction(function () use ($data, $locale, $cartData, $subtotal, $shipping) {
            $order = Order::query()->create([
                'number' => $this->generateOrderNumber(),
                'access_token' => (string) Str::uuid(),
                'status' => OrderStatus::Pending,
                'locale' => $locale,
                'currency' => config('shop.currency', 'PLN'),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'company' => $data['company'] ?? null,
                'street' => $data['street'],
                'city' => $data['city'],
                'postal_code' => $data['postal_code'],
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

            $order->loadMissing('items');
            Mail::to($order->email)->send(new OrderPlacedMail($order));

            return $order;
        });
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'XO-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
