<?php

namespace Tests\Feature;

use App\Jobs\DeliverPaymentStatusToCom;
use App\Jobs\ReconcileBrokerPayment;
use App\Models\BrokerPaymentEvent;
use App\Models\BrokerPaymentIntent;
use App\Services\Payments\BridgeSigner;
use App\Services\Payments\PaymentEventService;
use App\Services\Payments\PayUClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentBridgeTest extends TestCase
{
    private const INBOUND_SECRET = 'test-inbound-secret';

    private const OUTBOUND_SECRET = 'test-outbound-secret';

    private const SECOND_KEY = 'test-second-key';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('bridge_nonces');
        Schema::dropIfExists('broker_payment_events');
        Schema::dropIfExists('broker_payment_intents');
        (require database_path('migrations/2026_07_17_120000_create_payment_bridge_tables.php'))->up();

        config([
            'services.payment_bridge.enabled' => true,
            'services.payment_bridge.inbound_secret' => self::INBOUND_SECRET,
            'services.payment_bridge.outbound_secret' => self::OUTBOUND_SECRET,
            'services.payment_bridge.time_window' => 300,
            'services.payment_bridge.allowed_return_hosts' => ['xostore.com'],
            'services.payment_bridge.com_payment_url' => 'https://xostore.com/api/internal/v1/payments/events',
            'services.payu.environment' => 'sandbox',
            'services.payu.pos_id' => '123456',
            'services.payu.client_id' => 'client-id',
            'services.payu.client_secret' => 'client-secret',
            'services.payu.second_key' => self::SECOND_KEY,
            'services.payu.redirect_hosts' => ['secure.snd.payu.com'],
        ]);
        Cache::clear();
    }

    public function test_hmac_accepts_valid_request_rejects_bad_signature_and_replay(): void
    {
        Http::fake([
            '*/oauth/authorize' => Http::response(['access_token' => 'token']),
            '*/api/v2_1/orders' => Http::response([
                'orderId' => 'PAYU-1',
                'redirectUri' => 'https://secure.snd.payu.com/pay/1',
            ], 302, ['Location' => 'https://secure.snd.payu.com/pay/1']),
        ]);

        $body = json_encode($this->contract(), JSON_THROW_ON_ERROR);
        $nonce = Str::random(32);
        $headers = $this->bridgeHeaders($body, $nonce);

        $this->rawPost('/api/internal/v1/payments', $body, $headers)->assertCreated();
        $this->rawPost('/api/internal/v1/payments', $body, $headers)->assertConflict();

        $bad = $this->bridgeHeaders($body, Str::random(32));
        $bad['HTTP_X_BRIDGE_SIGNATURE'] = str_repeat('0', 64);
        $this->rawPost('/api/internal/v1/payments', $body, $bad)->assertUnauthorized();
    }

    public function test_create_is_idempotent_and_payu_uses_oauth_and_302_without_following_redirect(): void
    {
        Http::fake([
            '*/oauth/authorize' => Http::response(['access_token' => 'token']),
            '*/api/v2_1/orders' => Http::response([
                'orderId' => 'PAYU-2',
                'redirectUri' => 'https://secure.snd.payu.com/pay/2',
            ], 302, ['Location' => 'https://secure.snd.payu.com/pay/2']),
        ]);

        $body = json_encode($this->contract(), JSON_THROW_ON_ERROR);
        $first = $this->rawPost('/api/internal/v1/payments', $body, $this->bridgeHeaders($body, Str::random(32)));
        $second = $this->rawPost('/api/internal/v1/payments', $body, $this->bridgeHeaders($body, Str::random(32)));

        $first->assertCreated()
            ->assertJsonPath('provider_order_id', 'PAYU-2')
            ->assertJsonPath('redirect_url', 'https://secure.snd.payu.com/pay/2');
        $second->assertOk()->assertJsonPath('broker_payment_id', $first->json('broker_payment_id'));
        $this->assertDatabaseCount('broker_payment_intents', 1);

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/api/v2_1/orders')
            && $request['extOrderId'] === $this->contract()['payment_id']
            && $request['totalAmount'] === '2500'
            && $request['buyer']['firstName'] === 'Test'
            && $request['buyer']['lastName'] === 'Buyer');
    }

    public function test_failed_provider_creation_can_be_retried_idempotently(): void
    {
        Queue::fake();
        Http::fake([
            '*/oauth/authorize' => Http::response(['access_token' => 'token']),
            '*/api/v2_1/orders' => Http::sequence()
                ->push(['status' => ['statusCode' => 'ERROR']], 500)
                ->push([
                    'orderId' => 'PAYU-RETRY',
                    'redirectUri' => 'https://secure.snd.payu.com/pay/retry',
                ], 302, ['Location' => 'https://secure.snd.payu.com/pay/retry']),
        ]);

        $body = json_encode($this->contract(), JSON_THROW_ON_ERROR);
        $this->rawPost('/api/internal/v1/payments', $body, $this->bridgeHeaders($body, Str::random(32)))
            ->assertStatus(502);

        $this->rawPost('/api/internal/v1/payments', $body, $this->bridgeHeaders($body, Str::random(32)))
            ->assertOk()
            ->assertJsonPath('provider_order_id', 'PAYU-RETRY')
            ->assertJsonPath('redirect_url', 'https://secure.snd.payu.com/pay/retry');

        $this->assertDatabaseCount('broker_payment_intents', 1);
        $this->assertDatabaseCount('broker_payment_events', 1);
    }

    public function test_notifications_validate_signature_deduplicate_and_map_statuses(): void
    {
        Queue::fake();
        $intent = $this->intent();

        foreach ([
            'PENDING' => 'pending',
            'WAITING_FOR_CONFIRMATION' => 'waiting_for_confirmation',
            'COMPLETED' => 'paid',
        ] as $provider => $expected) {
            $body = $this->notificationBody($intent, $provider);
            $this->payuNotify($body)->assertOk();
            $this->assertSame($expected, $intent->fresh()->status);
        }

        $paidBody = $this->notificationBody($intent, 'CANCELED');
        $this->payuNotify($paidBody)->assertOk();
        $this->assertSame('paid', $intent->fresh()->status);
        $count = BrokerPaymentEvent::query()->count();
        $this->payuNotify($paidBody)->assertOk();
        $this->assertSame($count, BrokerPaymentEvent::query()->count());
        Queue::assertPushed(DeliverPaymentStatusToCom::class, 4);
    }

    public function test_notification_rejects_invalid_signature_and_order_mismatch(): void
    {
        Queue::fake();
        $intent = $this->intent();
        $body = $this->notificationBody($intent, 'COMPLETED');

        $this->call('POST', '/api/payu/notifications', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_OPENPAYU_SIGNATURE' => 'signature='.str_repeat('0', 32).';algorithm=MD5',
        ], $body)->assertUnauthorized();

        $mismatch = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        $mismatch['order']['totalAmount'] = '9999';
        $mismatchBody = json_encode($mismatch, JSON_THROW_ON_ERROR);
        $this->payuNotify($mismatchBody)->assertUnprocessable();
    }

    public function test_delivery_signs_exact_body_and_records_retry_state(): void
    {
        $intent = $this->intent(['status' => 'paid']);
        $event = $intent->events()->create([
            'event_id' => (string) Str::uuid(),
            'fingerprint' => hash('sha256', 'delivery'),
            'status' => 'paid',
            'payload' => [],
            'occurred_at' => now(),
        ]);
        $job = new DeliverPaymentStatusToCom($event);

        Http::fake(['*' => Http::sequence()
            ->push([], 500)
            ->push([], 200)]);
        try {
            $job->handle(app(BridgeSigner::class));
            $this->fail('Expected callback failure.');
        } catch (\Throwable) {
            $this->assertSame(1, $intent->fresh()->callback_delivery_attempts);
            $this->assertNotNull($intent->fresh()->callback_delivery_error);
        }

        $job->handle(app(BridgeSigner::class));
        $this->assertNotNull($intent->fresh()->callback_delivered_at);

        Http::assertSent(function (Request $request): bool {
            $timestamp = $request->header('X-Bridge-Timestamp')[0];
            $nonce = $request->header('X-Bridge-Nonce')[0];
            $expected = app(BridgeSigner::class)->sign(
                self::OUTBOUND_SECRET,
                $timestamp,
                $nonce,
                'POST',
                '/api/internal/v1/payments/events',
                $request->body(),
            );

            return hash_equals($expected, $request->header('X-Bridge-Signature')[0]);
        });
    }

    public function test_browser_return_only_redirects_to_current_allowlist_and_never_marks_paid(): void
    {
        $intent = $this->intent();
        $this->get(route('payu.return', [
            'brokerPaymentId' => $intent->broker_payment_id,
            'token' => $intent->return_token,
        ]))->assertRedirect($intent->return_url);
        $this->post(route('payu.return', [
            'brokerPaymentId' => $intent->broker_payment_id,
            'token' => $intent->return_token,
        ]))->assertRedirect($intent->return_url);
        $this->assertSame('pending', $intent->fresh()->status);

        config(['services.payment_bridge.allowed_return_hosts' => ['other.example']]);
        $this->get(route('payu.return', [
            'brokerPaymentId' => $intent->broker_payment_id,
            'token' => $intent->return_token,
        ]))->assertBadRequest();
    }

    public function test_reconciliation_fetches_order_and_queues_delivery(): void
    {
        Queue::fake();
        $intent = $this->intent();
        Http::fake([
            '*/oauth/authorize' => Http::response(['access_token' => 'token']),
            '*/api/v2_1/orders/*' => Http::response(['orders' => [[
                'merchantPosId' => '123456',
                'extOrderId' => $intent->source_payment_id,
                'orderId' => $intent->payu_order_id,
                'totalAmount' => (string) $intent->amount_minor,
                'currencyCode' => $intent->currency,
                'status' => 'COMPLETED',
            ]]]),
        ]);

        (new ReconcileBrokerPayment($intent->id))->handle(
            app(PayUClient::class),
            app(PaymentEventService::class),
        );

        $this->assertSame('paid', $intent->fresh()->status);
        Queue::assertPushed(DeliverPaymentStatusToCom::class);
    }

    private function contract(): array
    {
        return [
            'payment_id' => '0e9eebac-fb55-4eed-a5ce-0c9f4d38174f',
            'order_number' => 'XO-1001',
            'amount_minor' => 2500,
            'currency' => 'PLN',
            'locale' => 'pl',
            'return_url' => 'https://xostore.com/payment/return',
            'customer_ip' => '127.0.0.1',
            'buyer' => [
                'email' => 'buyer@example.com',
                'phone' => '+48123456789',
                'first_name' => 'Test',
                'last_name' => 'Buyer',
            ],
            'products' => [['name' => 'Product', 'unit_price_minor' => 2500, 'quantity' => 1]],
        ];
    }

    private function bridgeHeaders(string $body, string $nonce): array
    {
        $timestamp = (string) time();

        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BRIDGE_TIMESTAMP' => $timestamp,
            'HTTP_X_BRIDGE_NONCE' => $nonce,
            'HTTP_X_BRIDGE_SIGNATURE' => app(BridgeSigner::class)->sign(
                self::INBOUND_SECRET,
                $timestamp,
                $nonce,
                'POST',
                '/api/internal/v1/payments',
                $body,
            ),
        ];
    }

    private function rawPost(string $uri, string $body, array $headers)
    {
        return $this->call('POST', $uri, [], [], [], $headers, $body);
    }

    private function intent(array $overrides = []): BrokerPaymentIntent
    {
        return BrokerPaymentIntent::query()->create(array_merge([
            'broker_payment_id' => (string) Str::uuid(),
            'source_payment_id' => (string) Str::uuid(),
            'source_order_number' => 'XO-1002',
            'amount_minor' => 2500,
            'currency' => 'PLN',
            'locale' => 'pl',
            'return_url' => 'https://xostore.com/payment/return',
            'return_token' => Str::random(64),
            'customer_ip' => '127.0.0.1',
            'buyer' => ['email' => 'buyer@example.com', 'first_name' => 'Test', 'last_name' => 'Buyer'],
            'products' => [['name' => 'Product', 'unit_price_minor' => 2500, 'quantity' => 1]],
            'payu_order_id' => 'PAYU-'.Str::random(12),
            'status' => 'pending',
            'idempotency_key' => (string) Str::uuid(),
        ], $overrides));
    }

    private function notificationBody(BrokerPaymentIntent $intent, string $status): string
    {
        return json_encode(['order' => [
            'merchantPosId' => '123456',
            'extOrderId' => $intent->source_payment_id,
            'orderId' => $intent->payu_order_id,
            'totalAmount' => (string) $intent->amount_minor,
            'currencyCode' => $intent->currency,
            'status' => $status,
        ]], JSON_THROW_ON_ERROR);
    }

    private function payuNotify(string $body)
    {
        return $this->call('POST', '/api/payu/notifications', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_OPENPAYU_SIGNATURE' => 'signature='.md5($body.self::SECOND_KEY).';algorithm=MD5',
        ], $body);
    }
}
