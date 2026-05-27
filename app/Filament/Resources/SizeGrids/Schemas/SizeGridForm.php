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
            Section::make('Пресет размерной сетки')
                ->schema([
                    TextInput::make('code')
                        ->label('Код')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->maxLength(64),
                    TextInput::make('unit')
                        ->label('Единица (EU, US, см)')
                        ->maxLength(16),
                    Toggle::make('is_active')
                        ->label('Активна')
                        ->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Размеры')
                ->schema([
                    Repeater::make('values')
                        ->label('')
                        ->relationship()
                        ->schema([
                            TextInput::make('value')
                                ->label('Значение')
                                ->required()
                                ->maxLength(32),
                            TextInput::make('display_value')
                                ->label('Отображение')
                                ->maxLength(32),
                            TextInput::make('sort_order')
                                ->label('Порядок')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible()
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            TranslationTabs::make('size_grid'),
        ]);
    }
}
