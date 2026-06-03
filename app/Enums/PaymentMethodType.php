<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case Cod = 'cod';
    case BankTransfer = 'bank_transfer';
    case PaymentGateway = 'payment_gateway';

    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Наложенный платёж',
            self::BankTransfer => 'На счёт',
            self::PaymentGateway => 'Платёжная система (PayU)',
        };
    }
}
