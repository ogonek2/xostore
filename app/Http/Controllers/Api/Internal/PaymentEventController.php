<?php

namespace App\Http\Controllers\Api\Internal;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\PaymentEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class PaymentEventController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! is_int($request->input('amount_minor'))) {
            throw ValidationException::withMessages(['amount_minor' => ['Payment amount must be a JSON integer.']]);
        }

        $data = $request->validate([
            'event_id' => ['required', 'string', 'max:128'],
            'payment_id' => ['required', 'uuid'],
            'broker_payment_id' => ['required', 'string', 'max:255'],
            'provider_order_id' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(PaymentStatus::class)],
            'amount_minor' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'max:8'],
            'occurred_at' => ['required', 'date'],
        ]);

        $duplicate = PaymentEvent::query()->with('payment')->where('event_id', $data['event_id'])->first();

        if ($duplicate) {
            if ($duplicate->payment_id !== $data['payment_id']) {
                throw ValidationException::withMessages(['event_id' => ['Event ID belongs to another payment.']]);
            }

            $this->assertMatches($duplicate->payment, $data);

            return response()->json(['accepted' => true, 'duplicate' => true]);
        }

        DB::transaction(function () use ($data): void {
            $payment = Payment::query()->with('order')->lockForUpdate()->find($data['payment_id']);

            if (! $payment) {
                throw ValidationException::withMessages(['payment_id' => ['Unknown payment.']]);
            }

            $this->assertMatches($payment, $data);

            if (PaymentEvent::query()->where('event_id', $data['event_id'])->exists()) {
                return;
            }

            $occurredAt = Carbon::parse($data['occurred_at']);
            $status = PaymentStatus::from($data['status']);

            PaymentEvent::query()->create([
                'event_id' => $data['event_id'],
                'payment_id' => $payment->id,
                'status' => $status,
                'payload' => $this->redact($data),
                'occurred_at' => $occurredAt,
            ]);

            $isStale = $payment->last_event_at && $occurredAt->isBefore($payment->last_event_at);
            $wouldRollbackPaid = $payment->status === PaymentStatus::Paid && $status !== PaymentStatus::Paid;

            if ($isStale || $wouldRollbackPaid) {
                return;
            }

            $updates = [
                'broker_payment_id' => $payment->broker_payment_id ?: $data['broker_payment_id'],
                'provider_order_id' => $payment->provider_order_id ?: ($data['provider_order_id'] ?? null),
                'status' => $status,
                'last_event_at' => $occurredAt,
            ];

            if ($status === PaymentStatus::Paid) {
                $updates['paid_at'] = $occurredAt;
                $confirmed = OrderStatus::forCode('confirmed');
                if ($confirmed) {
                    $payment->order->update(['order_status_id' => $confirmed->id]);
                }
            } elseif ($status === PaymentStatus::Failed) {
                $updates['failed_at'] = $occurredAt;
            } elseif ($status === PaymentStatus::Cancelled) {
                $updates['cancelled_at'] = $occurredAt;
            }

            $payment->update($updates);
        });

        return response()->json(['accepted' => true]);
    }

    private function assertMatches(Payment $payment, array $data): void
    {
        $mismatches = [];

        if ($payment->amount_minor !== (int) $data['amount_minor']) {
            $mismatches['amount_minor'] = ['Payment amount does not match.'];
        }
        if (strtoupper($payment->currency) !== strtoupper($data['currency'])) {
            $mismatches['currency'] = ['Payment currency does not match.'];
        }
        if ($payment->broker_payment_id && ! hash_equals($payment->broker_payment_id, $data['broker_payment_id'])) {
            $mismatches['broker_payment_id'] = ['Broker payment ID does not match.'];
        }
        if ($payment->provider_order_id && ! hash_equals($payment->provider_order_id, (string) ($data['provider_order_id'] ?? ''))) {
            $mismatches['provider_order_id'] = ['Provider order ID does not match.'];
        }

        if ($mismatches) {
            throw ValidationException::withMessages($mismatches);
        }
    }

    private function redact(array $payload): array
    {
        $sensitive = ['email', 'phone', 'first_name', 'last_name', 'customer_ip', 'authorization', 'secret'];

        foreach ($payload as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitive, true)) {
                $payload[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $payload[$key] = $this->redact($value);
            }
        }

        return $payload;
    }
}
