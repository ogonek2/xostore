<?php

namespace App\Enums;

enum ConsultationStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::Contacted => 'На связи',
            self::Closed => 'Закрыта',
        };
    }
}
