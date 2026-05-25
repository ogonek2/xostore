<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Filament\Forms\TranslationTabs;
use App\Models\Brand;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Produkt')
                    ->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64),
                        Select::make('status')
                            ->label('Status')
                            ->options(collect(ProductStatus::cases())->mapWithKeys(
                                fn (ProductStatus $status) => [$status->value => ucfirst($status->value)]
                            ))
                            ->required()
                            ->native(false),
                        Select::make('type')
                            ->label('Typ produktu')
                            ->options(collect(ProductType::cases())->mapWithKeys(
                                fn (ProductType $type) => [$type->value => ucfirst($type->value)]
                            ))
                            ->required()
                            ->native(false),
                        Select::make('brand_id')
                            ->label('Marka')
                            ->relationship('brand', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Brand $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('primary_category_id')
                            ->label('Główna kategoria')
                            ->relationship('primaryCategory', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('base_price')
                            ->label('Cena bazowa')
                            ->numeric()
                            ->prefix('PLN')
                            ->minValue(0),
                        TextInput::make('compare_at_price')
                            ->label('Cena przed promocją')
                            ->numeric()
                            ->prefix('PLN')
                            ->minValue(0),
                        TextInput::make('weight_grams')
                            ->label('Waga (g)')
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_featured')
                            ->label('Wyróżniony'),
                        Toggle::make('is_new')
                            ->label('Nowość (nowynki)'),
                        Toggle::make('track_inventory')
                            ->label('Śledź stany')
                            ->default(true),
                        DateTimePicker::make('published_at')
                            ->label('Data publikacji')
                            ->seconds(false),
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
                            ->columnSpanFull(),
                        Select::make('catalogs')
                            ->label('Katalogi')
                            ->relationship('catalogs', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Catalog $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Warianty')
                    ->schema([
                        Repeater::make('variants')
                            ->label('')
                            ->relationship()
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU wariantu')
                                    ->required()
                                    ->maxLength(64),
                                TextInput::make('price')
                                    ->label('Cena')
                                    ->numeric()
                                    ->required()
                                    ->prefix('PLN'),
                                TextInput::make('compare_at_price')
                                    ->label('Cena promo')
                                    ->numeric()
                                    ->prefix('PLN'),
                                TextInput::make('stock_qty')
                                    ->label('Stan')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Toggle::make('is_default')
                                    ->label('Domyślny'),
                                Toggle::make('is_active')
                                    ->label('Aktywny')
                                    ->default(true),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['sku'] ?? null),
                    ])
                    ->columnSpanFull(),
                TranslationTabs::make('product'),
            ]);
    }
}
