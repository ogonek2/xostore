<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use App\Enums\CatalogType;
use App\Filament\Forms\TranslationTabs;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
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
                Section::make('Ustawienia katalogu')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kod')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->alphaDash(),
                        Select::make('type')
                            ->label('Typ katalogu')
                            ->options(collect(CatalogType::cases())->mapWithKeys(
                                fn (CatalogType $type) => [$type->value => $type->label()]
                            ))
                            ->required()
                            ->live()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label('Kolejność')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktywny')
                            ->default(true),
                        Toggle::make('show_on_homepage')
                            ->label('Na stronie głównej')
                            ->default(false),
                        DateTimePicker::make('published_at')
                            ->label('Data publikacji')
                            ->seconds(false),
                        FileUpload::make('image_path')
                            ->label('Okładka')
                            ->image()
                            ->directory('catalogs')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Powiązania')
                    ->schema([
                        Select::make('categories')
                            ->label('Kategorie')
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
                            ->label('Produkty')
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
