<?php

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\PaymentMethod;

final class PaymentRedirectBuilder
{
    public function build(PaymentMethod $method, Order $order): ?string
    {
        if (! $method->isGateway() || blank($method->redirect_url)) {
            return null;
        }

        $replacements = [
            '{{order_number}}' => $order->number,
            '{{total}}' => number_format((float) $order->total, 2, '.', ''),
            '{{total_minor}}' => (string) (int) round((float) $order->total * 100),
            '{{subtotal}}' => number_format((float) $order->subtotal, 2, '.', ''),
            '{{shipping}}' => number_format((float) $order->shipping, 2, '.', ''),
            '{{currency}}' => $order->currency,
            '{{email}}' => rawurlencode($order->email),
            '{{phone}}' => rawurlencode((string) $order->phone),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $method->redirect_url);
    }

    public function bankInstructions(PaymentMethod $method, Order $order): array
    {
        $noteTemplate = $method->payment_note_template ?? 'Nr zamówienia: {{order_number}}';

        return [
            'recipient' => $method->bank_recipient,
            'bank_name' => $method->bank_name,
            'account' => $method->bank_account,
            'payment_note' => str_replace('{{order_number}}', $order->number, $noteTemplate),
            'total' => $order->total,
        ];
    }
}
