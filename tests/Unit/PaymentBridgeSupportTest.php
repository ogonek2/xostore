<?php

namespace Tests\Unit;

use App\Support\Payments\BridgeSignature;
use App\Support\Payments\MinorUnits;
use PHPUnit\Framework\TestCase;

class PaymentBridgeSupportTest extends TestCase
{
    public function test_builds_the_documented_canonical_signature(): void
    {
        $body = '{"payment_id":"abc"}';
        $canonical = "1720000000\nnonce-1234567890\nPOST\n/api/internal/v1/payments/events\n".hash('sha256', $body);

        $this->assertSame($canonical, BridgeSignature::canonical(
            '1720000000',
            'nonce-1234567890',
            'post',
            'api/internal/v1/payments/events',
            $body,
        ));
        $this->assertSame(
            hash_hmac('sha256', $canonical, 'test-secret'),
            BridgeSignature::sign('test-secret', '1720000000', 'nonce-1234567890', 'POST', '/api/internal/v1/payments/events', $body),
        );
    }

    public function test_converts_decimal_money_without_float_arithmetic(): void
    {
        $this->assertSame(12345, MinorUnits::fromDecimal('123.45'));
        $this->assertSame(100, MinorUnits::fromDecimal('1'));
        $this->assertSame(105, MinorUnits::fromDecimal('1.05'));
    }
}
