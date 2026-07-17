<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case WaitingForConfirmation = 'waiting_for_confirmation';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled, self::Failed], true);
    }
}
