<?php

namespace App\Jobs;

use App\Models\BrokerPaymentEvent;
use App\Services\Payments\BridgeSigner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DeliverPaymentStatusToCom implements ShouldQueue
{
    use Queueable;

    public int $tries = 8;

    public int $timeout = 30;

    public function __construct(public readonly BrokerPaymentEvent $event)
    {
        $this->onQueue('payments');
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [30, 120, 300, 900, 1800, 3600];
    }

    public function handle(BridgeSigner $signer): void
    {
        $event = $this->event->fresh(['intent']);
        $intent = $event?->intent;
        $url = (string) config('services.payment_bridge.com_payment_url');
        $secret = (string) config('services.payment_bridge.outbound_secret');

        if (! $event || ! $intent || $url === '' || $secret === '') {
            throw new RuntimeException('Payment callback configuration is incomplete.');
        }

        $payload = [
            'event_id' => $event->event_id,
            'payment_id' => $intent->source_payment_id,
            'broker_payment_id' => $intent->broker_payment_id,
            'provider_order_id' => $intent->payu_order_id,
            'status' => $event->status,
            'amount_minor' => $intent->amount_minor,
            'currency' => $intent->currency,
            'occurred_at' => $event->occurred_at->toIso8601String(),
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $nonce = (string) Str::uuid();
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $signature = $signer->sign($secret, $timestamp, $nonce, 'POST', $path, $body);

        $intent->increment('callback_delivery_attempts');

        try {
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Bridge-Timestamp' => $timestamp,
                'X-Bridge-Nonce' => $nonce,
                'X-Bridge-Signature' => $signature,
            ])
                ->connectTimeout(3)
                ->timeout(10)
                ->withBody($body, 'application/json')
                ->post($url)
                ->throw();

            $intent->update([
                'callback_delivery_error' => null,
                'callback_delivered_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $intent->update([
                'callback_delivery_error' => Str::limit($exception->getMessage(), 1000),
            ]);

            throw $exception;
        }
    }
}
