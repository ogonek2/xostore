<?php

namespace App\Filament\Resources\Commerce\Schemas;

use App\Filament\Forms\LocalizedGroupFields;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ShopCommerceSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('commerce')
                ->tabs([
                    Tab::make('delivery')
                        ->label('Доставка')
                        ->schema([
                            Toggle::make('delivery_enabled')
                                ->label('Показывать блок доставки на карточке и странице товара')
                                ->live(),
                            Section::make('Тексты доставки')
                                ->description('Эти тексты выводятся покупателю. Стоимость доставки и бесплатная доставка подтягиваются из активных способов оплаты (методы с включённой доставкой).')
                                ->schema(LocalizedGroupFields::sections('delivery', [
                                    ['name' => 'title', 'label' => 'Заголовок', 'maxLength' => 80],
                                    ['name' => 'summary', 'label' => 'Кратко', 'type' => 'textarea', 'rows' => 2],
                                    ['name' => 'details', 'label' => 'Детали (каждая строка = пункт)', 'type' => 'textarea', 'rows' => 5],
                                ]))
                                ->visible(fn ($get) => $get('delivery_enabled'))
                                ->columnSpanFull(),
                        ]),
                    Tab::make('payment')
                        ->label('Оплата')
                        ->schema([
                            Toggle::make('payment_enabled')
                                ->label('Показывать блок оплаты на карточке и странице товара')
                                ->live(),
                            Section::make('Тексты оплаты')
                                ->description('Список доступных способов оплаты берётся динамически из раздела «Способы оплаты».')
                                ->schema(LocalizedGroupFields::sections('payment', [
                                    ['name' => 'title', 'label' => 'Заголовок', 'maxLength' => 80],
                                    ['name' => 'summary', 'label' => 'Кратко', 'type' => 'textarea', 'rows' => 2],
                                ]))
                                ->visible(fn ($get) => $get('payment_enabled'))
                                ->columnSpanFull(),
                            Placeholder::make('payment_methods_hint')
                                ->label('Способы оплаты')
                                ->content('Редактируются в Настройки → Способы оплаты. Активные методы автоматически появятся на товаре.')
                                ->visible(fn ($get) => $get('payment_enabled')),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
