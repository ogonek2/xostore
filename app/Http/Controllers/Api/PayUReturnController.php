<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrokerPaymentIntent;
use App\Services\Payments\PaymentEventService;
use App\Services\Payments\PayUClient;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PayUReturnController extends Controller
{
    public function __invoke(
        string $brokerPaymentId,
        string $token,
        PayUClient $payu,
        PaymentEventService $events,
    ): RedirectResponse {
        $intent = BrokerPaymentIntent::query()
            ->where('broker_payment_id', $brokerPaymentId)
            ->firstOrFail();

        abort_unless(hash_equals($intent->return_token, $token), 404);

        $host = parse_url($intent->return_url, PHP_URL_HOST);
        $scheme = parse_url($intent->return_url, PHP_URL_SCHEME);
        abort_unless(
            $scheme === 'https'
            && is_string($host)
            && in_array(strtolower($host), config('services.payment_bridge.allowed_return_hosts', []), true),
            400,
            'Invalid return URL.',
        );

        if ($intent->payu_order_id) {
            try {
                $response = $payu->getOrder($intent->payu_order_id);
                $order = $response['orders'][0] ?? $response['order'] ?? null;

                if (is_array($order)) {
                    $fingerprint = hash('sha256', 'return:'.json_encode([
                        $order['orderId'] ?? null,
                        $order['status'] ?? null,
                        $order['totalAmount'] ?? null,
                        $order['currencyCode'] ?? null,
                    ], JSON_THROW_ON_ERROR));

                    $events->accept($order, $fingerprint);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->away($intent->return_url);
    }
}
