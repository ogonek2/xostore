<?php

namespace App\Services\Payments;

use App\Jobs\DeliverPaymentStatusToCom;
use App\Models\BrokerPaymentEvent;
use App\Models\BrokerPaymentIntent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentEventService
{
    private const STATUSES = [
        'PENDING' => 'pending',
        'WAITING_FOR_CONFIRMATION' => 'waiting_for_confirmation',
        'COMPLETED' => 'paid',
        'CANCELED' => 'cancelled',
    ];

    public function accept(array $order, string $fingerprint): ?BrokerPaymentEvent
    {
        $existingEvent = BrokerPaymentEvent::query()
            ->with('intent')
            ->where('fingerprint', $fingerprint)
            ->first();

        if ($existingEvent) {
            if (! $existingEvent->intent->callback_delivered_at) {
                $this->deliver($existingEvent);
            }

            return null;
        }

        $providerStatus = (string) ($order['status'] ?? '');
        $status = self::STATUSES[$providerStatus] ?? null;
        if ($status === null) {
            throw ValidationException::withMessages(['status' => 'Unsupported PayU order status.']);
        }

        try {
            $event = DB::transaction(function () use ($order, $fingerprint, $status, $providerStatus) {
                $intent = BrokerPaymentIntent::query()
                    ->where('source_payment_id', (string) ($order['extOrderId'] ?? ''))
                    ->lockForUpdate()
                    ->first();

                if (! $intent
                    || ! hash_equals((string) config('services.payu.pos_id'), (string) ($order['merchantPosId'] ?? ''))
                    || ! hash_equals((string) $intent->payu_order_id, (string) ($order['orderId'] ?? ''))
                    || (string) $intent->amount_minor !== (string) ($order['totalAmount'] ?? '')
                    || ! hash_equals($intent->currency, (string) ($order['currencyCode'] ?? ''))) {
                    throw ValidationException::withMessages(['order' => 'PayU order does not match the payment intent.']);
                }

                $effectiveStatus = $intent->status === 'paid' ? 'paid' : $status;
                $occurredAt = now();
                $updates = [
                    'status' => $effectiveStatus,
                    'last_event_at' => $occurredAt,
                    'callback_delivered_at' => null,
                    'callback_delivery_error' => null,
                ];
                if ($effectiveStatus === 'paid' && ! $intent->paid_at) {
                    $updates['paid_at'] = $occurredAt;
                }
                if ($effectiveStatus === 'cancelled' && ! $intent->cancelled_at) {
                    $updates['cancelled_at'] = $occurredAt;
                }
                $intent->update($updates);

                $event = $intent->events()->create([
                    'event_id' => (string) Str::uuid(),
                    'fingerprint' => $fingerprint,
                    'status' => $effectiveStatus,
                    'payload' => [
                        'orderId' => (string) $order['orderId'],
                        'extOrderId' => (string) $order['extOrderId'],
                        'merchantPosId' => (string) $order['merchantPosId'],
                        'totalAmount' => (string) $order['totalAmount'],
                        'currencyCode' => (string) $order['currencyCode'],
                        'providerStatus' => $providerStatus,
                    ],
                    'occurred_at' => $occurredAt,
                ]);

                return $event;
            });

            $this->deliver($event);

            return $event;
        } catch (UniqueConstraintViolationException $exception) {
            if (BrokerPaymentEvent::query()->where('fingerprint', $fingerprint)->exists()) {
                return null;
            }

            throw $exception;
        }
    }

    private function deliver(BrokerPaymentEvent $event): void
    {
        try {
            (new DeliverPaymentStatusToCom($event))->handle(app(BridgeSigner::class));
        } catch (Throwable $exception) {
            report($exception);
            DeliverPaymentStatusToCom::dispatch($event);

            throw $exception;
        }
    }
}
