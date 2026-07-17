<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Mail\OrderStatusEmailNotifier;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status_id')) {
            return;
        }

        $previousStatusId = $order->getOriginal('order_status_id');
        $orderId = $order->getKey();

        DB::afterCommit(function () use ($orderId, $previousStatusId): void {
            $committedOrder = Order::query()->find($orderId);

            if ($committedOrder) {
                app(OrderStatusEmailNotifier::class)->notifyIfConfigured(
                    $committedOrder,
                    $previousStatusId ? (int) $previousStatusId : null,
                );
            }
        });
    }
}
