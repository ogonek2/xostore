<?php

namespace App\Enums;

enum NewsletterCampaignStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Sent => 'Отправлена',
        };
    }
}
