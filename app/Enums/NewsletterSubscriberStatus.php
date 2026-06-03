<?php

namespace App\Enums;

enum NewsletterSubscriberStatus: string
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';

    public function label(): string
    {
        return match ($this) {
            self::Subscribed => 'Подписан',
            self::Unsubscribed => 'Отписан',
            self::Bounced => 'Недоставляемый',
        };
    }
}
