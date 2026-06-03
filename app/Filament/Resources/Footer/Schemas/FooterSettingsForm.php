<?php

namespace App\Filament\Resources\Footer\Schemas;

use App\Filament\Forms\LocalizedGroupFields;
use App\Filament\Forms\NavItemLabelFields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class FooterSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('footer')
                ->tabs([
                    Tab::make('newsletter')
                        ->label('Newsletter')
                        ->schema([
                            Toggle::make('newsletter_enabled')
                                ->label('Показывать блок подписки')
                                ->live(),
                            Section::make('Тексты newsletter')
                                ->schema(LocalizedGroupFields::sections('newsletter', [
                                    ['name' => 'eyebrow', 'label' => 'Надпись сверху', 'maxLength' => 80],
                                    ['name' => 'title', 'label' => 'Заголовок', 'maxLength' => 160],
                                    ['name' => 'hint', 'label' => 'Подсказка', 'type' => 'textarea', 'rows' => 2],
                                    ['name' => 'placeholder', 'label' => 'Placeholder e-mail', 'maxLength' => 120],
                                    ['name' => 'submit', 'label' => 'Кнопка', 'maxLength' => 40],
                                    ['name' => 'success', 'label' => 'Успех', 'maxLength' => 200],
                                    ['name' => 'error', 'label' => 'Ошибка', 'maxLength' => 200],
                                ]))
                                ->visible(fn ($get) => $get('newsletter_enabled'))
                                ->columnSpanFull(),
                        ]),
                    Tab::make('brand')
                        ->label('Бренд')
                        ->schema([
                            Section::make('Логотип и описание')
                                ->description('Название магазина берётся из SHOP_NAME. Здесь — слоган под логотипом.')
                                ->schema(LocalizedGroupFields::sections('brand', [
                                    ['name' => 'tagline', 'label' => 'Слоган', 'type' => 'textarea', 'rows' => 3],
                                ]))
                                ->columnSpanFull(),
                        ]),
                    Tab::make('social')
                        ->label('Соцсети')
                        ->schema([
                            Toggle::make('social_enabled')->label('Показывать блок')->live(),
                            ...LocalizedGroupFields::sections('social', [
                                ['name' => 'title', 'label' => 'Заголовок блока', 'maxLength' => 80],
                            ]),
                            Repeater::make('social.links')
                                ->label('Ссылки')
                                ->schema([
                                    Select::make('network')
                                        ->label('Сеть')
                                        ->options([
                                            'instagram' => 'Instagram',
                                            'facebook' => 'Facebook',
                                            'pinterest' => 'Pinterest',
                                            'youtube' => 'YouTube',
                                            'tiktok' => 'TikTok',
                                            'link' => 'Другая',
                                        ])
                                        ->required()
                                        ->native(false),
                                    TextInput::make('url')
                                        ->label('URL')
                                        ->url()
                                        ->required()
                                        ->maxLength(500),
                                    ...NavItemLabelFields::make('labels'),
                                    Toggle::make('is_active')->label('Активна')->default(true),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel('Добавить ссылку')
                                ->reorderable()
                                ->collapsible()
                                ->visible(fn ($get) => $get('social_enabled'))
                                ->columnSpanFull(),
                        ]),
                    Tab::make('contact')
                        ->label('Контакт')
                        ->schema([
                            Toggle::make('contact_enabled')->label('Показывать блок')->live(),
                            Section::make('Подписи')
                                ->description('E-mail и телефон из .env (SHOP_CONTACT_EMAIL, SHOP_PHONE).')
                                ->schema(LocalizedGroupFields::sections('contact', [
                                    ['name' => 'title', 'label' => 'Заголовок колонки', 'maxLength' => 80],
                                    ['name' => 'email_label', 'label' => 'Подпись e-mail', 'maxLength' => 40],
                                    ['name' => 'phone_label', 'label' => 'Подпись телефона', 'maxLength' => 40],
                                ]))
                                ->visible(fn ($get) => $get('contact_enabled'))
                                ->columnSpanFull(),
                        ]),
                    Tab::make('payments')
                        ->label('Оплата')
                        ->schema([
                            Toggle::make('payments_enabled')->label('Показывать блок')->live(),
                            ...LocalizedGroupFields::sections('payments', [
                                ['name' => 'title', 'label' => 'Заголовок', 'maxLength' => 80],
                            ]),
                            Repeater::make('payments.methods')
                                ->label('Способы оплаты (бейджи)')
                                ->schema([
                                    TextInput::make('label')
                                        ->label('Название')
                                        ->required()
                                        ->maxLength(40),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel('Добавить')
                                ->reorderable()
                                ->visible(fn ($get) => $get('payments_enabled'))
                                ->columnSpanFull(),
                        ]),
                    Tab::make('bottom')
                        ->label('Низ футера')
                        ->schema([
                            Section::make('Копирайт')
                                ->schema(LocalizedGroupFields::sections('bottom', [
                                    ['name' => 'copyright', 'label' => 'Текст после года', 'maxLength' => 120],
                                ]))
                                ->columnSpanFull(),
                            Repeater::make('bottom.links')
                                ->label('Ссылки (политика, условия…)')
                                ->schema([
                                    ...NavItemLabelFields::make('labels'),
                                    TextInput::make('url')
                                        ->label('Ссылка')
                                        ->maxLength(500)
                                        ->required()
                                        ->helperText('Относительный путь: regulamin или полный URL'),
                                    Toggle::make('open_in_new_tab')
                                        ->label('Новая вкладка')
                                        ->default(false),
                                    Toggle::make('is_active')
                                        ->label('Активна')
                                        ->default(true),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel('Добавить ссылку')
                                ->reorderable()
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
