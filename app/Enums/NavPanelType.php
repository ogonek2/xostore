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
            self::Category => 'Категория (подкатегории и/или товары)',
            self::SelectedProducts => 'Выбранные товары',
            self::Brands => 'Бренды (#теги)',
            self::CatalogProducts => 'Товары из каталога',
            self::PromotionProducts => 'Товары по акциям',
            self::Links => 'Произвольные ссылки',
        };
    }
}
