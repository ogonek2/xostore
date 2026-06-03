<?php

namespace App\Services\Mail;

use App\Models\Order;
use App\Models\OrderStatus;

final class OrderStatusEmailRenderer
{
    public function render(string $template, Order $order, OrderStatus $status): string
    {
        $replacements = [
            '{{order_number}}' => $order->number,
            '{{customer_name}}' => $order->displayName(),
            '{{phone}}' => (string) $order->phone,
            '{{email}}' => $order->email,
            '{{city}}' => (string) $order->city,
            '{{total}}' => number_format((float) $order->total, 2, ',', ' '),
            '{{status}}' => $status->label($order->locale),
            '{{currency}}' => $order->currency,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
