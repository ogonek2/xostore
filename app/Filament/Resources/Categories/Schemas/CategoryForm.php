<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CategoryType;
use App\Filament\Forms\TranslationTabs;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
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
                Section::make('Podstawowe')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kod')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->alphaDash(),
                        Select::make('parent_id')
                            ->label('Kategoria nadrzędna')
                            ->relationship('parent', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('type')
                            ->label('Typ')
                            ->options(collect(CategoryType::cases())->mapWithKeys(
                                fn (CategoryType $type) => [$type->value => config("shop.category.types.{$type->value}", $type->value)]
                            ))
                            ->required()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label('Kolejność')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('Aktywna')
                            ->default(true),
                        Toggle::make('show_in_menu')
                            ->label('W menu')
                            ->default(true),
                        FileUpload::make('image_path')
                            ->label('Zdjęcie')
                            ->image()
                            ->directory('categories')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                TranslationTabs::make('category'),
            ]);
    }
}
