<?php

namespace App\Services\Checkout;

use App\Models\PaymentMethod;

final class OrderShippingCalculator
{
    public function calculate(PaymentMethod $method, float $subtotal): float
    {
        if (! $method->shipping_enabled) {
            return 0.0;
        }

        if (
            $method->free_shipping_enabled
            && $method->free_shipping_from !== null
            && $subtotal >= (float) $method->free_shipping_from
        ) {
            return 0.0;
        }

        return (float) $method->shipping_cost;
    }
}
