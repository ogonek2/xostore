<?php

namespace App\Enums;

enum NavPanelType: string
{
    case Category = 'category';
    case SelectedProducts = 'selected_products';
    case Brands = 'brands';
    case CatalogProducts = 'catalog_products';
    case PromotionProducts = 'promotion_products';
    case Links = 'links';

    public function label(): string
    {
        return match ($this) {
            self::Category => 'Категории (одна или несколько ссылок)',
            self::SelectedProducts => 'Выбранные товары',
            self::Brands => 'Бренды (#теги)',
            self::CatalogProducts => 'Каталоги (ссылки или превью товаров)',
            self::PromotionProducts => 'Товары по акциям',
            self::Links => 'Произвольные ссылки',
        };
    }
}
