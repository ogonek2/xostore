<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Support\Payments\BridgeSignature;
use App\Support\Payments\MinorUnits;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Str;
use RuntimeException;

final class ShopPaymentBrokerClient
{
    public function __construct(private HttpFactory $http) {}

    /**
     * @return array{broker_payment_id:string,provider_order_id:?string,redirect_url:string,status:string}
     */
    public function create(Payment $payment, string $customerIp): array
    {
        if (! config('services.payment_bridge.enabled')) {
            throw new RuntimeException('Payment bridge is disabled.');
        }

        $payment->loadMissing('order.items');
        $order = $payment->order;
        $path = '/api/internal/v1/payments';
        $baseUrl = rtrim((string) config('services.payment_bridge.shop_url'), '/');
        $secret = (string) config('services.payment_bridge.outbound_secret');

        if ($baseUrl === '' || $secret === '') {
            throw new RuntimeException('Payment bridge is not configured.');
        }

        $names = preg_split('/\s+/', trim($order->customer_name ?? ''), 2);
        $products = $order->items->map(fn ($item) => [
            'name' => $item->product_name,
            'unit_price_minor' => MinorUnits::fromDecimal($item->unit_price),
            'quantity' => $item->quantity,
        ])->values();

        $productsMinor = $products->sum(
            fn (array $product): int => $product['unit_price_minor'] * $product['quantity']
        );
        $adjustmentMinor = $payment->amount_minor - $productsMinor;

        if ($adjustmentMinor < 0) {
            throw new RuntimeException('Payment products exceed the order total.');
        }

        if ($adjustmentMinor > 0) {
            $products->push([
                'name' => 'Shipping and order adjustment',
                'unit_price_minor' => $adjustmentMinor,
                'quantity' => 1,
            ]);
        }

        $payload = [
            'payment_id' => $payment->id,
            'order_number' => $order->number,
            'amount_minor' => $payment->amount_minor,
            'currency' => $payment->currency,
            'locale' => $order->locale,
            'return_url' => route('payments.return', [
                'locale' => $order->locale,
                'payment' => $payment->public_token,
            ]),
            'customer_ip' => $customerIp,
            'buyer' => [
                'email' => $order->email,
                'phone' => (string) $order->phone,
                'first_name' => $names[0] ?? '',
                'last_name' => $names[1] ?? '',
            ],
            'products' => $products->all(),
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $timestamp = (string) time();
        $nonce = (string) Str::uuid();
        $response = $this->http
            ->timeout(10)
            ->connectTimeout(3)
            ->acceptJson()
            ->withHeaders([
                'X-Bridge-Timestamp' => $timestamp,
                'X-Bridge-Nonce' => $nonce,
                'X-Bridge-Signature' => BridgeSignature::sign($secret, $timestamp, $nonce, 'POST', $path, $body),
                'Idempotency-Key' => $payment->idempotency_key,
            ])
            ->withBody($body, 'application/json')
            ->post($baseUrl.$path)
            ->throw();

        $data = $response->json();
        $status = PaymentStatus::tryFrom((string) ($data['status'] ?? ''));
        $redirectUrl = (string) ($data['redirect_url'] ?? $data['redirect_uri'] ?? '');
        $host = strtolower((string) parse_url($redirectUrl, PHP_URL_HOST));
        $allowedHosts = array_map('strtolower', (array) config('services.payment_bridge.allowed_redirect_hosts', []));

        if (
            blank($data['broker_payment_id'] ?? null)
            || ! $status
            || ! in_array($host, $allowedHosts, true)
            || parse_url($redirectUrl, PHP_URL_SCHEME) !== 'https'
        ) {
            throw new RuntimeException('Invalid payment broker response.');
        }

        return [
            'broker_payment_id' => (string) $data['broker_payment_id'],
            'provider_order_id' => filled($data['provider_order_id'] ?? null) ? (string) $data['provider_order_id'] : null,
            'redirect_url' => $redirectUrl,
            'status' => $status->value,
        ];
    }
}
