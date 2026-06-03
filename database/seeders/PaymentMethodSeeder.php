<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::query()->updateOrCreate(
            ['code' => 'cod'],
            [
                'type' => PaymentMethodType::Cod,
                'labels' => ['pl' => 'Płatność przy odbiorze', 'en' => 'Cash on delivery'],
                'instructions' => [
                    'pl' => 'Zapłacisz kurierowi przy dostawie zamówienia.',
                    'en' => 'Pay the courier when you receive your order.',
                ],
                'is_active' => true,
                'sort_order' => 0,
                'shipping_enabled' => true,
                'shipping_cost' => 15,
                'free_shipping_enabled' => true,
                'free_shipping_from' => 500,
            ],
        );

        PaymentMethod::query()->updateOrCreate(
            ['code' => 'bank'],
            [
                'type' => PaymentMethodType::BankTransfer,
                'labels' => ['pl' => 'Przelew na konto', 'en' => 'Bank transfer'],
                'instructions' => [
                    'pl' => 'W tytule przelewu podaj numer zamówienia z potwierdzenia.',
                    'en' => 'Use your order number as the transfer reference.',
                ],
                'is_active' => true,
                'sort_order' => 1,
                'shipping_enabled' => true,
                'shipping_cost' => 15,
                'free_shipping_enabled' => true,
                'free_shipping_from' => 500,
                'bank_recipient' => config('shop.name'),
                'bank_name' => 'Bank',
                'bank_account' => 'PL00 0000 0000 0000 0000 0000 0000',
                'payment_note_template' => 'Zamówienie {{order_number}}',
            ],
        );

        PaymentMethod::query()->updateOrCreate(
            ['code' => 'payu'],
            [
                'type' => PaymentMethodType::PaymentGateway,
                'labels' => ['pl' => 'PayU — karta / BLIK', 'en' => 'PayU — card / BLIK'],
                'instructions' => [
                    'pl' => 'Po złożeniu zamówienia przejdziesz do bezpiecznej płatności PayU.',
                    'en' => 'You will be redirected to PayU secure payment after placing the order.',
                ],
                'is_active' => true,
                'sort_order' => 2,
                'shipping_enabled' => true,
                'shipping_cost' => 0,
                'free_shipping_enabled' => false,
                'free_shipping_from' => null,
                'redirect_url' => 'https://secure.payu.com/pay/?extOrderId={{order_number}}&amount={{total_minor}}&currency={{currency}}',
            ],
        );
    }
}
