<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Courier = 'courier';
    case Paczkomat = 'paczkomat';

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return match ($this) {
            self::Courier => $locale === 'en' ? 'Courier' : 'Kurier',
            self::Paczkomat => 'Paczkomat',
        };
    }
}
