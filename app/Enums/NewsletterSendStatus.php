<?php

namespace App\Enums;

enum NewsletterSendStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В очереди',
            self::Sent => 'Отправлено',
            self::Failed => 'Ошибка',
            self::Skipped => 'Пропущено',
        };
    }
}
