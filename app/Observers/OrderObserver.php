<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        if ($order->status !== OrderStatus::Shipped) {
            return;
        }

        $previousStatus = $order->getOriginal('status');
        if ($previousStatus === OrderStatus::Shipped->value) {
            return;
        }

        $order->loadMissing('items');
        Mail::to($order->email)->send(new OrderShippedMail($order));
    }
}
