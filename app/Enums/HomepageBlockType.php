<?php

namespace App\Enums;

enum HomepageBlockType: string
{
    case Hero = 'hero';
    case Banners = 'banners';
    case CategoryShowcase = 'category_showcase';
    case Trending = 'trending';
    case Promotions = 'promotions';
    case NewArrivals = 'new_arrivals';
    case Catalog = 'catalog';
    case Spacer = 'spacer';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero-баннер',
            self::Banners => 'Баннеры',
            self::CategoryShowcase => 'Лента категорий',
            self::Trending => 'Тренды',
            self::Promotions => 'Акции',
            self::NewArrivals => 'Новинки',
            self::Catalog => 'Товары из каталога',
            self::Spacer => 'Отступ',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Hero => 'Верхний баннер. Контент редактируется в разделе «Hero-баннеры».',
            self::Banners => 'Сетка баннеров под hero. Контент — в разделе «Баннеры».',
            self::CategoryShowcase => 'Горизонтальная карусель категорий.',
            self::Trending => 'Карусель товаров из каталога «Тренды».',
            self::Promotions => 'Блок акций с главной страницы.',
            self::NewArrivals => 'Карусель новинок из каталога «Новинки».',
            self::Catalog => 'Произвольная подборка товаров из выбранного каталога.',
            self::Spacer => 'Пустое пространство между блоками.',
        };
    }

    /**
     * @return list<self>
     */
    public static function defaults(): array
    {
        return [
            self::Hero,
            self::Banners,
            self::CategoryShowcase,
            self::Trending,
            self::Promotions,
            self::NewArrivals,
        ];
    }
}
