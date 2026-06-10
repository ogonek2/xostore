<?php

namespace App\Enums;

enum SocialNetwork: string
{
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case Pinterest = 'pinterest';
    case Youtube = 'youtube';
    case Tiktok = 'tiktok';
    case Linkedin = 'linkedin';
    case X = 'x';
    case Threads = 'threads';
    case Snapchat = 'snapchat';
    case Whatsapp = 'whatsapp';
    case Telegram = 'telegram';
    case Viber = 'viber';
    case Messenger = 'messenger';
    case Signal = 'signal';
    case Discord = 'discord';
    case Skype = 'skype';
    case Line = 'line';
    case Wechat = 'wechat';
    case Link = 'link';

    public function label(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::Facebook => 'Facebook',
            self::Pinterest => 'Pinterest',
            self::Youtube => 'YouTube',
            self::Tiktok => 'TikTok',
            self::Linkedin => 'LinkedIn',
            self::X => 'X (Twitter)',
            self::Threads => 'Threads',
            self::Snapchat => 'Snapchat',
            self::Whatsapp => 'WhatsApp',
            self::Telegram => 'Telegram',
            self::Viber => 'Viber',
            self::Messenger => 'Messenger',
            self::Signal => 'Signal',
            self::Discord => 'Discord',
            self::Skype => 'Skype',
            self::Line => 'LINE',
            self::Wechat => 'WeChat',
            self::Link => 'Другая ссылка',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::Whatsapp, self::Telegram, self::Viber, self::Messenger, self::Signal, self::Skype, self::Line, self::Wechat => 'messengers',
            self::Link => 'other',
            default => 'social',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $groups = [
            'social' => 'Социальные сети',
            'messengers' => 'Мессенджеры',
            'other' => 'Прочее',
        ];

        $options = [];

        foreach ($groups as $groupKey => $groupLabel) {
            foreach (self::cases() as $case) {
                if ($case->group() !== $groupKey) {
                    continue;
                }

                $options[$case->value] = $groupLabel.': '.$case->label();
            }
        }

        return $options;
    }
}
