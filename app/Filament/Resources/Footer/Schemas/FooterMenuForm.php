<?php

namespace App\Filament\Resources\Footer\Schemas;

use App\Filament\Forms\NavItemLabelFields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FooterMenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Колонки ссылок')
                ->description('Каждый пункт верхнего уровня — колонка. Внутри — ссылки. URL: относительный путь (produkty) или полный https://…')
                ->schema([
                    Repeater::make('items')
                        ->label('')
                        ->relationship('items')
                        ->schema([
                            ...NavItemLabelFields::make(),
                            TextInput::make('url')
                                ->label('Ссылка колонки (необязательно)')
                                ->maxLength(500)
                                ->helperText('Обычно пусто — заголовок колонки без ссылки'),
                            Toggle::make('is_active')
                                ->label('Активна')
                                ->default(true),
                            Repeater::make('children')
                                ->label('Ссылки')
                                ->relationship('children')
                                ->schema([
                                    ...NavItemLabelFields::make(),
                                    TextInput::make('url')
                                        ->label('Ссылка')
                                        ->maxLength(500)
                                        ->required(),
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
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => static::previewLabel($state))
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Добавить колонку')
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => static::previewLabel($state))
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }

    protected static function previewLabel(array $state): ?string
    {
        $labels = $state['labels'] ?? [];
        $default = (string) config('shop.default_language', 'pl');

        return $labels[$default]
            ?? ($labels ? (string) reset($labels) : null)
            ?? 'Колонка';
    }
}
