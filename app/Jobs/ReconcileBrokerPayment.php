<?php

namespace App\Jobs;

use App\Models\BrokerPaymentIntent;
use App\Services\Payments\PaymentEventService;
use App\Services\Payments\PayUClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class ReconcileBrokerPayment implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly int $intentId)
    {
        $this->onQueue('payments');
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(PayUClient $payu, PaymentEventService $events): void
    {
        $intent = BrokerPaymentIntent::query()->find($this->intentId);
        if (! $intent || ! in_array($intent->status, ['pending', 'waiting_for_confirmation'], true) || ! $intent->payu_order_id) {
            return;
        }

        $response = $payu->getOrder($intent->payu_order_id);
        $order = $response['orders'][0] ?? $response['order'] ?? null;
        if (! is_array($order)) {
            throw new RuntimeException('PayU reconciliation response did not contain an order.');
        }

        $fingerprint = hash('sha256', 'reconcile:'.json_encode([
            $order['orderId'] ?? null,
            $order['status'] ?? null,
            $order['totalAmount'] ?? null,
            $order['currencyCode'] ?? null,
        ], JSON_THROW_ON_ERROR));

        $events->accept($order, $fingerprint);
    }
}
