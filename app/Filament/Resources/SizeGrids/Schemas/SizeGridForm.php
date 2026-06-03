<?php

namespace App\Filament\Resources\SizeGrids\Schemas;

use App\Filament\Forms\TranslationTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SizeGridForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Справочник пресета')
                ->description('Кнопки rozmiaru na stronie produktu (S, M, L, 38…). Tabela mierki w cm — osobny moduł «Tabele mierok (cm)».')
                ->schema([
                    TextInput::make('code')
                        ->label('Код (латиница)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->maxLength(64)
                        ->helperText('Напр. clothing_letter_women, clothing_letter_men, footwear_eu, denim_waist'),
                    TextInput::make('unit')
                        ->label('Подпись единицы')
                        ->placeholder('EU, US, см')
                        ->maxLength(16)
                        ->helperText('Показывается рядом с таблицей мерок на сайте'),
                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Размеры для кнопок на сайте')
                ->description('«Код» — внутреннее значение. «На сайте» — текст на кнопке (S, M, 38…).')
                ->schema([
                    Repeater::make('values')
                        ->label('')
                        ->relationship()
                        ->schema([
                            TextInput::make('value')
                                ->label('Код размера')
                                ->required()
                                ->maxLength(32)
                                ->placeholder('s, m, 38'),
                            TextInput::make('display_value')
                                ->label('На сайте')
                                ->maxLength(32)
                                ->placeholder('S, M, 38'),
                            TextInput::make('sort_order')
                                ->label('Порядок')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Добавить размер')
                        ->collapsible()
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            TranslationTabs::make('size_grid', 'Название и описание'),
        ]);
    }
}
