<?php

namespace App\Filament\Resources\HeroBanners\Schemas;

use App\Support\Shop\HeroBannerFrame;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HeroBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Секция hero-баннера')
                    ->description('Настройки слайда: сетка на витрине, порядок в карусели. Карточки с изображениями и текстами — в блоке ниже.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Название (админка)')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(80),
                        Select::make('layout')
                            ->label('Сетка на витрине')
                            ->options([
                                'single' => '1 баннер',
                                'two_columns' => '2 баннера (2 колонки)',
                                'three_columns' => '3 баннера (3 колонки)',
                                'feature_stack' => '1 большой + 2 в колонке',
                            ])
                            ->default('single')
                            ->required()
                            ->native(false)
                            ->helperText('Порядок карточек в сетке — как иконки на экране телефона: сверху вниз, слева направо.'),
                        Select::make('height_preset')
                            ->label('Высота баннера')
                            ->options(HeroBannerFrame::heightOptions())
                            ->default('auto')
                            ->required()
                            ->native(false)
                            ->helperText('«Авто» — высота по картинке без пустых полос. В слайдере с несколькими баннерами «Авто» заменяется на среднюю высоту.'),
                        Select::make('width_preset')
                            ->label('Ширина баннера')
                            ->options(HeroBannerFrame::widthOptions())
                            ->default('full')
                            ->required()
                            ->native(false),
                        Select::make('image_fit')
                            ->label('Как вписать картинку')
                            ->options(HeroBannerFrame::fitOptions())
                            ->default('contain')
                            ->required()
                            ->native(false)
                            ->helperText('Для сеток 2–3 колонки лучше «Заполнить». Для одного баннера — «Вписать целиком».'),
                        TextInput::make('sort_order')
                            ->label('Порядок слайда')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
