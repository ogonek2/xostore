<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает',
            self::Confirmed => 'Подтверждён',
            self::Processing => 'В обработке',
            self::Shipped => 'Отправлен',
            self::Completed => 'Завершён',
            self::Cancelled => 'Отменён',
        };
    }
}
