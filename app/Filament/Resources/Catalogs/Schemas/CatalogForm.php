<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use App\Enums\CatalogType;
use App\Filament\Forms\TranslationTabs;
use App\Filament\Support\FilamentMedia;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Настройки каталога')
                    ->schema([
                        TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->alphaDash(),
                        Select::make('type')
                            ->label('Тип каталога')
                            ->options(collect(CatalogType::cases())->mapWithKeys(
                                fn (CatalogType $type) => [$type->value => $type->label()]
                            ))
                            ->required()
                            ->live()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                        Toggle::make('show_on_homepage')
                            ->label('На главной')
                            ->default(false),
                        DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->seconds(false),
                        FilamentMedia::image('image_path', 'catalogs')
                            ->label('Обложка')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Связи')
                    ->schema([
                        Select::make('categories')
                            ->label('Категории')
                            ->relationship('categories', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (Get $get): bool => in_array($get('type'), [
                                CatalogType::Categories->value,
                                CatalogType::Mixed->value,
                            ], true))
                            ->columnSpanFull(),
                        Select::make('products')
                            ->label('Товары')
                            ->relationship('products', 'sku')
                            ->getOptionLabelFromRecordUsing(
                                fn (Product $record) => ($record->translate('name', 'pl') ?? $record->sku).' ('.$record->sku.')'
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (Get $get): bool => in_array($get('type'), [
                                CatalogType::Manual->value,
                                CatalogType::Mixed->value,
                            ], true))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                TranslationTabs::make('catalog'),
            ]);
    }
}
