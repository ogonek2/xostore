<?php

namespace App\Filament\Resources\Homepage\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HomepageSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Блоки на главной')
                ->description('Отключите ненужные секции — они не будут выводиться на витрине.')
                ->schema([
                    Toggle::make('show_category_showcase')
                        ->label('Категории (горизонтальная лента)')
                        ->default(true),
                    Toggle::make('show_trending_section')
                        ->label('Тренды')
                        ->default(true),
                    Toggle::make('show_promotions_section')
                        ->label('Акции')
                        ->default(true),
                    Toggle::make('show_new_arrivals_section')
                        ->label('Новинки')
                        ->default(true),
                    Toggle::make('show_banners_section')
                        ->label('Баннеры под героем')
                        ->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Лента категорий')
                ->description('Выберите категории и порядок карточек. Если у категории нет обложки, подставится фото одного из товаров.')
                ->schema([
                    Repeater::make('category_showcase')
                        ->label('Категории')
                        ->schema([
                            Select::make('category_id')
                                ->label('Категория')
                                ->options(
                                    Category::query()
                                        ->orderBy('sort_order')
                                        ->get()
                                        ->mapWithKeys(fn (Category $category) => [
                                            $category->id => ($category->translate('name', 'pl') ?? $category->code).' ('.$category->code.')',
                                        ])
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false),
                            Select::make('sublabel_key')
                                ->label('Подпись под названием')
                                ->options([
                                    'for_women' => 'Для женщин',
                                    'for_men' => 'Для мужчин',
                                ])
                                ->nullable()
                                ->native(false),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Добавить категорию')
                        ->reorderable()
                        ->collapsible()
                        ->columnSpanFull(),
                ])
                ->visible(fn ($get) => (bool) $get('show_category_showcase'))
                ->columnSpanFull(),
        ]);
    }
}
