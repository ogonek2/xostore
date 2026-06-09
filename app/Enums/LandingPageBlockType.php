<?php

namespace App\Enums;

enum LandingPageBlockType: string
{
    case Hero = 'hero';
    case Heading = 'heading';
    case RichText = 'rich_text';
    case TextImage = 'text_image';
    case Media = 'media';
    case Gallery = 'gallery';
    case Cta = 'cta';
    case Faq = 'faq';
    case Features = 'features';
    case Products = 'products';
    case Spacer = 'spacer';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero (баннер)',
            self::Heading => 'Заголовок',
            self::RichText => 'Текст (редактор)',
            self::TextImage => 'Текст + картинка',
            self::Media => 'Медиа (фото/видео)',
            self::Gallery => 'Галерея',
            self::Cta => 'Призыв к действию (CTA)',
            self::Faq => 'FAQ',
            self::Features => 'Преимущества / карточки',
            self::Products => 'Товары (карусель)',
            self::Spacer => 'Отступ',
        };
    }

    public function usesItemsRepeater(): bool
    {
        return in_array($this, [self::Gallery, self::Faq, self::Features], true);
    }

    public function usesTranslationFields(): bool
    {
        return $this !== self::Spacer;
    }
}
