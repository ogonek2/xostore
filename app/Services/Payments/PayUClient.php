<?php

namespace App\Services\Payments;

use App\Models\BrokerPaymentIntent;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayUClient
{
    public function createOrder(BrokerPaymentIntent $intent): array
    {
        $response = Http::acceptJson()
            ->connectTimeout(3)
            ->timeout(15)
            ->withToken($this->accessToken())
            ->withOptions(['allow_redirects' => false])
            ->post($this->baseUrl().'/api/v2_1/orders', [
                'notifyUrl' => route('payu.notifications'),
                'continueUrl' => route('payu.return', [
                    'brokerPaymentId' => $intent->broker_payment_id,
                    'token' => $intent->return_token,
                ]),
                'customerIp' => $intent->customer_ip,
                'merchantPosId' => (string) config('services.payu.pos_id'),
                'description' => 'Order '.$intent->source_order_number,
                'currencyCode' => $intent->currency,
                'totalAmount' => (string) $intent->amount_minor,
                'extOrderId' => $intent->source_payment_id,
                'buyer' => array_filter([
                    'email' => $intent->buyer['email'],
                    'phone' => $intent->buyer['phone'] ?? null,
                    'firstName' => $intent->buyer['first_name'] ?? null,
                    'lastName' => $intent->buyer['last_name'] ?? null,
                    'language' => $intent->locale,
                ], fn (mixed $value): bool => $value !== null && $value !== ''),
                'products' => collect($intent->products)->map(fn (array $product) => [
                    'name' => $product['name'],
                    'unitPrice' => (string) $product['unit_price_minor'],
                    'quantity' => (string) $product['quantity'],
                ])->all(),
            ]);

        if (! in_array($response->status(), [200, 201, 302], true)) {
            $response->throw();
        }

        $data = $this->json($response);
        $redirectUri = $data['redirectUri'] ?? $response->header('Location');
        $orderId = $data['orderId'] ?? null;

        if (! is_string($redirectUri) || ! is_string($orderId) || ! $this->allowedRedirect($redirectUri)) {
            throw new RuntimeException('PayU returned an invalid order response.');
        }

        return ['order_id' => $orderId, 'redirect_uri' => $redirectUri];
    }

    public function getOrder(string $orderId): array
    {
        $response = Http::acceptJson()
            ->connectTimeout(3)
            ->timeout(15)
            ->withToken($this->accessToken())
            ->withOptions(['allow_redirects' => false])
            ->get($this->baseUrl().'/api/v2_1/orders/'.rawurlencode($orderId))
            ->throw();

        return $response->json();
    }

    private function accessToken(): string
    {
        $cached = Cache::get('payu.oauth.access-token');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $clientId = (string) config('services.payu.client_id');
        $secret = (string) config('services.payu.client_secret');

        if ($clientId === '' || $secret === '') {
            throw new RuntimeException('PayU OAuth credentials are not configured.');
        }

        $response = Http::asForm()
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(10)
            ->retry(2, 200)
            ->withOptions(['allow_redirects' => false])
            ->post($this->baseUrl().'/pl/standard/user/oauth/authorize', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $secret,
            ])->throw();

        $token = $response->json('access_token');
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayU OAuth response did not contain an access token.');
        }

        $ttl = max(60, (int) $response->json('expires_in', 3600) - 60);
        Cache::put('payu.oauth.access-token', $token, $ttl);

        return $token;
    }

    private function baseUrl(): string
    {
        return config('services.payu.environment') === 'production'
            ? 'https://secure.payu.com'
            : 'https://secure.snd.payu.com';
    }

    private function allowedRedirect(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host)
            && in_array(strtolower($host), config('services.payu.redirect_hosts', []), true);
    }

    private function json(Response $response): array
    {
        $data = $response->json();

        return is_array($data) ? $data : [];
    }
}
