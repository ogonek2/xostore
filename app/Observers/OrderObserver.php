<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Mail\OrderStatusEmailNotifier;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status_id')) {
            return;
        }

        $previousStatusId = $order->getOriginal('order_status_id');

        app(OrderStatusEmailNotifier::class)->notifyIfConfigured(
            $order,
            $previousStatusId ? (int) $previousStatusId : null,
        );
    }
}
