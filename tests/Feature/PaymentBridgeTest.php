<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Services\Payments\ShopPaymentBrokerClient;
use App\Support\Payments\BridgeSignature;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payment_bridge.enabled' => true,
            'services.payment_bridge.shop_url' => 'https://payments.example.test',
            'services.payment_bridge.inbound_secret' => 'inbound-test-secret',
            'services.payment_bridge.outbound_secret' => 'outbound-test-secret',
            'services.payment_bridge.time_window' => 300,
            'services.payment_bridge.allowed_redirect_hosts' => ['secure.snd.payu.com', 'secure.payu.com'],
        ]);
    }

    public function test_order_exposes_payment_assignment_and_relation(): void
    {
        $order = $this->makeOrder();
        $payment = $this->makePayment($order);

        $this->assertContains('payment_method_id', $order->getFillable());
        $this->assertTrue($order->payments->contains($payment));
    }

    public function test_event_requires_signature_and_is_idempotent(): void
    {
        $payment = $this->makePayment();
        $payload = $this->eventPayload($payment, 'paid');

        $this->postJson('/api/internal/v1/payments/events', $payload)->assertUnauthorized();
        $this->signedEvent($payload)->assertOk()->assertJson(['accepted' => true]);
        $this->signedEvent($payload)->assertOk()->assertJson(['duplicate' => true]);

        $this->assertDatabaseCount('payment_events', 1);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame('confirmed', $payment->order->fresh()->orderStatus->code);
    }

    public function test_event_rejects_mismatches_and_cannot_roll_paid_back(): void
    {
        $payment = $this->makePayment();
        $bad = $this->eventPayload($payment, 'paid', ['event_id' => 'event-bad', 'amount_minor' => 999]);
        $this->signedEvent($bad)->assertUnprocessable();

        $this->signedEvent($this->eventPayload($payment, 'paid'))->assertOk();
        $rollback = $this->eventPayload($payment, 'pending', [
            'event_id' => 'event-rollback',
            'occurred_at' => now()->addSecond()->toIso8601String(),
        ]);
        $this->signedEvent($rollback)->assertOk();

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertDatabaseCount('payment_events', 2);
    }

    public function test_client_signs_exact_body_and_validates_payu_redirect(): void
    {
        $payment = $this->makePayment();
        OrderItem::query()->create([
            'order_id' => $payment->order_id,
            'product_name' => 'Test product',
            'variant_sku' => 'SKU-1',
            'quantity' => 2,
            'unit_price' => '61.72',
            'total_price' => '123.44',
        ]);

        Http::fake([
            'https://payments.example.test/*' => Http::response([
                'broker_payment_id' => 'broker-1',
                'provider_order_id' => 'payu-1',
                'redirect_url' => 'https://secure.snd.payu.com/order/abc',
                'status' => 'waiting_for_confirmation',
            ]),
        ]);

        $result = app(ShopPaymentBrokerClient::class)->create($payment, '127.0.0.1');

        $this->assertSame('broker-1', $result['broker_payment_id']);
        Http::assertSent(function ($request) use ($payment) {
            $timestamp = $request->header('X-Bridge-Timestamp')[0];
            $nonce = $request->header('X-Bridge-Nonce')[0];
            $expected = BridgeSignature::sign(
                'outbound-test-secret',
                $timestamp,
                $nonce,
                'POST',
                '/api/internal/v1/payments',
                $request->body(),
            );

            return $request->url() === 'https://payments.example.test/api/internal/v1/payments'
                && $request->header('X-Bridge-Signature')[0] === $expected
                && $request->header('Idempotency-Key')[0] === $payment->idempotency_key
                && $request['amount_minor'] === 12345
                && $request['products'][0]['unit_price_minor'] === 6172
                && collect($request['products'])->sum(
                    fn (array $product): int => $product['unit_price_minor'] * $product['quantity']
                ) === $request['amount_minor'];
        });
    }

    public function test_retry_reuses_payment_and_idempotency_key(): void
    {
        $payment = $this->makePayment();
        Http::fake([
            'https://payments.example.test/*' => Http::response([
                'broker_payment_id' => 'broker-retry',
                'provider_order_id' => 'payu-retry',
                'redirect_url' => 'https://secure.payu.com/order/retry',
                'status' => 'waiting_for_confirmation',
            ]),
        ]);

        $this->post(route('payments.retry', [
            'locale' => 'pl',
            'order' => $payment->order->access_token,
            'payment' => $payment->public_token,
        ]))->assertRedirect('https://secure.payu.com/order/retry');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
        Http::assertSent(fn ($request) => $request->header('Idempotency-Key')[0] === $payment->idempotency_key);
    }

    public function test_retry_after_cancellation_creates_a_new_payment_attempt(): void
    {
        $payment = $this->makePayment();
        $payment->update([
            'status' => PaymentStatus::Cancelled,
            'provider_order_id' => 'cancelled-payu-order',
        ]);

        Http::fake([
            'https://payments.example.test/*' => Http::response([
                'broker_payment_id' => 'broker-new-attempt',
                'provider_order_id' => 'payu-new-attempt',
                'redirect_url' => 'https://secure.payu.com/order/new-attempt',
                'status' => 'pending',
            ]),
        ]);

        $this->post(route('payments.retry', [
            'locale' => 'pl',
            'order' => $payment->order->access_token,
            'payment' => $payment->public_token,
        ]))->assertRedirect('https://secure.payu.com/order/new-attempt');

        $this->assertDatabaseCount('payments', 2);
        Http::assertSent(fn ($request) => $request['payment_id'] !== $payment->id);
    }

    public function test_thank_you_page_shows_payment_status_and_retry(): void
    {
        $this->seed(LanguageSeeder::class);
        $payment = $this->makePayment();

        $this->get(route('checkout.thankyou', [
            'locale' => 'pl',
            'order' => $payment->order->access_token,
        ]))
            ->assertOk()
            ->assertSee('Status płatności')
            ->assertSee('Ponów płatność');
    }

    private function signedEvent(array $payload)
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $nonce = (string) Str::uuid();
        $signature = BridgeSignature::sign(
            'inbound-test-secret',
            $timestamp,
            $nonce,
            'POST',
            '/api/internal/v1/payments/events',
            $body,
        );

        return $this->call('POST', '/api/internal/v1/payments/events', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_BRIDGE_TIMESTAMP' => $timestamp,
            'HTTP_X_BRIDGE_NONCE' => $nonce,
            'HTTP_X_BRIDGE_SIGNATURE' => $signature,
        ], $body);
    }

    private function eventPayload(Payment $payment, string $status, array $overrides = []): array
    {
        return array_replace([
            'event_id' => 'event-1',
            'payment_id' => $payment->id,
            'broker_payment_id' => 'broker-1',
            'provider_order_id' => 'payu-1',
            'status' => $status,
            'amount_minor' => $payment->amount_minor,
            'currency' => $payment->currency,
            'occurred_at' => now()->toIso8601String(),
        ], $overrides);
    }

    private function makePayment(?Order $order = null): Payment
    {
        $order ??= $this->makeOrder();

        return Payment::query()->create([
            'id' => (string) Str::uuid(),
            'public_token' => Str::random(48),
            'order_id' => $order->id,
            'provider' => 'payu',
            'amount_minor' => 12345,
            'currency' => 'PLN',
            'status' => PaymentStatus::Pending,
            'idempotency_key' => (string) Str::uuid(),
        ]);
    }

    private function makeOrder(): Order
    {
        return Order::query()->create([
            'number' => strtoupper(Str::random(8)),
            'access_token' => (string) Str::uuid(),
            'order_status_id' => OrderStatus::forCode('pending')->id,
            'locale' => 'pl',
            'currency' => 'PLN',
            'email' => 'buyer@example.test',
            'phone' => '+48123456789',
            'customer_name' => 'Jan Kowalski',
            'city' => 'Warsaw',
            'delivery_address' => 'Test 1',
            'country' => 'PL',
            'subtotal' => '123.45',
            'shipping' => '0.00',
            'total' => '123.45',
            'placed_at' => now(),
        ]);
    }
}
