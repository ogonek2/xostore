<?php

namespace App\Filament\Resources\SizeChartPresets\Schemas;

use App\Filament\Forms\TranslationTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SizeChartPresetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Пресет визуальной таблицы мерок')
                ->description('Точные значения в сантиметрах для таблицы на странице товара. Кнопки S/M/L задаются отдельно в «Размерные сетки».')
                ->schema([
                    TextInput::make('code')
                        ->label('Код (латиница)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->maxLength(64)
                        ->helperText('Напр. women_tops_cm, men_trousers_cm'),
                    TextInput::make('unit')
                        ->label('Единица')
                        ->default('cm')
                        ->maxLength(16)
                        ->helperText('Отображается в заголовке таблицы на сайте'),
                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Строки таблицы (см)')
                ->schema([
                    Repeater::make('rows')
                        ->label('')
                        ->relationship()
                        ->schema([
                            TextInput::make('size')
                                ->label('Размер')
                                ->required()
                                ->maxLength(32)
                                ->placeholder('S, M, 38'),
                            TextInput::make('chest_cm')
                                ->label('Грудь (см)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(300)
                                ->step(0.5),
                            TextInput::make('waist_cm')
                                ->label('Талия (см)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(300)
                                ->step(0.5),
                            TextInput::make('hips_cm')
                                ->label('Бёдра (см)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(300)
                                ->step(0.5),
                            TextInput::make('inseam_cm')
                                ->label('Внутр. шов (см)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(200)
                                ->step(0.5),
                            TextInput::make('sort_order')
                                ->label('Порядок')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Добавить строку')
                        ->collapsible()
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            TranslationTabs::make('size_chart_preset', 'Название и описание'),
        ]);
    }
}
