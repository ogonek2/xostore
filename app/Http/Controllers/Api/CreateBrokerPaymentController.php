<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DeliverPaymentStatusToCom;
use App\Models\BrokerPaymentIntent;
use App\Services\Payments\PayUClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateBrokerPaymentController extends Controller
{
    public function __invoke(Request $request, PayUClient $payu): JsonResponse
    {
        $data = $request->validate([
            'payment_id' => ['required', 'uuid'],
            'order_number' => ['required', 'string', 'max:100'],
            'amount_minor' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'currency' => ['required', 'string', 'size:3', 'uppercase'],
            'locale' => ['required', 'string', 'max:10'],
            'return_url' => ['required', 'url', 'max:2048'],
            'customer_ip' => ['required', 'ip'],
            'buyer' => ['required', 'array'],
            'buyer.email' => ['required', 'email:rfc', 'max:255'],
            'buyer.phone' => ['nullable', 'string', 'max:32'],
            'buyer.first_name' => ['required', 'string', 'max:100'],
            'buyer.last_name' => ['nullable', 'string', 'max:100'],
            'products' => ['required', 'array', 'min:1', 'max:100'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.unit_price_minor' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'products.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        if (! $this->allowedReturnUrl($data['return_url'])) {
            throw ValidationException::withMessages(['return_url' => 'Return URL host is not allowed.']);
        }

        $calculated = collect($data['products'])->sum(
            fn (array $product) => $product['unit_price_minor'] * $product['quantity']
        );
        if ($calculated !== $data['amount_minor']) {
            throw ValidationException::withMessages(['amount_minor' => 'Amount does not match products total.']);
        }

        $intent = BrokerPaymentIntent::query()->firstOrCreate(
            ['source_payment_id' => $data['payment_id']],
            [
                'broker_payment_id' => (string) Str::uuid(),
                'source_order_number' => $data['order_number'],
                'amount_minor' => $data['amount_minor'],
                'currency' => strtoupper($data['currency']),
                'locale' => $data['locale'],
                'return_url' => $data['return_url'],
                'return_token' => Str::random(64),
                'customer_ip' => $data['customer_ip'],
                'buyer' => $data['buyer'],
                'products' => $data['products'],
                'status' => 'pending',
                'idempotency_key' => $data['payment_id'],
            ],
        );

        if (! $intent->wasRecentlyCreated && (
            $intent->source_order_number !== $data['order_number']
            || $intent->amount_minor !== $data['amount_minor']
            || $intent->currency !== strtoupper($data['currency'])
            || $intent->return_url !== $data['return_url']
        )) {
            throw ValidationException::withMessages([
                'payment_id' => 'Payment ID was already used with a different contract.',
            ]);
        }

        $claimedProviderCreation = BrokerPaymentIntent::query()
            ->whereKey($intent->id)
            ->whereNull('payu_order_id')
            ->whereNull('redirect_uri')
            ->whereIn('status', ['pending', 'failed'])
            ->update(['status' => 'creating']) === 1;

        if ($claimedProviderCreation) {
            try {
                $order = $payu->createOrder($intent);
                BrokerPaymentIntent::query()->whereKey($intent->id)->update([
                    'payu_order_id' => $order['order_id'],
                    'redirect_uri' => $order['redirect_uri'],
                    'status' => 'pending',
                ]);
            } catch (Throwable $exception) {
                $intent->update(['status' => 'failed']);
                $eventId = (string) Str::uuid();
                $event = $intent->events()->create([
                    'event_id' => $eventId,
                    'fingerprint' => hash('sha256', 'create-failed:'.$intent->broker_payment_id.':'.$eventId),
                    'status' => 'failed',
                    'payload' => ['reason' => 'provider_unavailable'],
                    'occurred_at' => now(),
                ]);
                DeliverPaymentStatusToCom::dispatch($event)->afterCommit();
                report($exception);

                return response()->json([
                    'message' => 'Payment provider is unavailable.',
                    'broker_payment_id' => $intent->broker_payment_id,
                    'status' => 'failed',
                ], 502);
            }
        }

        $intent->refresh();
        if ($intent->status === 'creating') {
            return response()->json([
                'message' => 'Payment is being initialized.',
                'broker_payment_id' => $intent->broker_payment_id,
                'status' => 'pending',
            ], 409);
        }

        return response()->json([
            'broker_payment_id' => $intent->broker_payment_id,
            'payment_id' => $intent->source_payment_id,
            'provider_order_id' => $intent->payu_order_id,
            'redirect_url' => $intent->redirect_uri,
            'status' => $intent->status,
        ], $intent->wasRecentlyCreated ? 201 : 200);
    }

    private function allowedReturnUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        return $scheme === 'https'
            && is_string($host)
            && in_array(strtolower($host), config('services.payment_bridge.allowed_return_hosts', []), true);
    }
}
