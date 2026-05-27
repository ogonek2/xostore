<?php

namespace App\Enums;

enum ShopEventType: string
{
    case PageView = 'page_view';
    case ProductView = 'product_view';
    case AddToCart = 'add_to_cart';
    case RemoveFromCart = 'remove_from_cart';
    case CheckoutStart = 'checkout_start';
    case OrderPlaced = 'order_placed';

    public function label(): string
    {
        return match ($this) {
            self::PageView => 'Просмотр страницы',
            self::ProductView => 'Просмотр товара',
            self::AddToCart => 'В корзину',
            self::RemoveFromCart => 'Удаление из корзины',
            self::CheckoutStart => 'Начало оформления',
            self::OrderPlaced => 'Заказ оформлен',
        };
    }
}
