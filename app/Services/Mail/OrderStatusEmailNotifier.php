<?php

namespace App\Services\Mail;

use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

final class OrderStatusEmailNotifier
{
    public function __construct(
        protected OrderStatusEmailRenderer $renderer,
    ) {}

    public function notifyIfConfigured(Order $order, ?int $previousStatusId = null): void
    {
        $order->loadMissing(['orderStatus.emailTemplate']);

        $status = $order->orderStatus;

        if (! $status || $previousStatusId === $status->id) {
            return;
        }

        $template = $status->emailTemplate;

        if (! $template?->is_active || blank($template->message) || blank($order->email)) {
            return;
        }

        $subject = $this->renderer->render(
            $template->subject ?: 'Zamówienie {{order_number}}',
            $order,
            $status,
        );
        $body = $this->renderer->render($template->message, $order, $status);

        Mail::to($order->email)->send(new OrderStatusChangedMail($order, $subject, $body));
    }
}
