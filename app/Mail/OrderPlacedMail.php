<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.order_placed.subject', ['number' => $this->order->number]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.placed',
            with: [
                'order' => $this->order,
                'contactEmail' => config('shop.contact.email'),
                'contactPhone' => config('shop.contact.phone'),
                'formattedTotal' => $this->formatAmount($this->order->total),
            ],
        );
    }

    protected function formatAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').' '.config('shop.currency_symbol', 'zł');
    }
}
