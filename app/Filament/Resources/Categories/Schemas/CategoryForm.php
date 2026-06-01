<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CategoryType;
use App\Filament\Forms\TranslationTabs;
use App\Filament\Support\FilamentMedia;
use App\Models\Category;
use App\Models\SizeGrid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Основное')
                    ->schema([
                        TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->alphaDash(),
                        Select::make('parent_id')
                            ->label('Родительская категория')
                            ->relationship('parent', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('type')
                            ->label('Тип')
                            ->options(collect(CategoryType::cases())->mapWithKeys(
                                fn (CategoryType $type) => [$type->value => $type->label()]
                            ))
                            ->required()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                        Toggle::make('show_in_menu')
                            ->label('В меню')
                            ->default(true),
                        FilamentMedia::image('image_path', 'categories')
                            ->label('Изображение')
                            ->columnSpanFull(),
                        Select::make('sizeGrids')
                            ->label('Размерные сетки')
                            ->relationship('sizeGrids', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (SizeGrid $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                TranslationTabs::make('category'),
            ]);
    }
}
