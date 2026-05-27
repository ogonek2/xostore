<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Enums\PromotionLayout;
use App\Enums\PromotionProductTargetType;
use App\Filament\Forms\TranslationTabs;
use App\Models\Catalog;
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

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Настройки акции')
                    ->schema([
                        TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->alphaDash(),
                        Select::make('layout')
                            ->label('Макет карточки')
                            ->options(collect(PromotionLayout::cases())->mapWithKeys(
                                fn (PromotionLayout $layout) => [$layout->value => $layout->label()]
                            ))
                            ->required()
                            ->native(false),
                        TextInput::make('discount_percent')
                            ->label('Скидка (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Скидка автоматически применяется к цене в корзине'),
                        Select::make('product_target_type')
                            ->label('Товары со скидкой')
                            ->options(collect(PromotionProductTargetType::cases())->mapWithKeys(
                                fn (PromotionProductTargetType $type) => [$type->value => $type->label()]
                            ))
                            ->native(false)
                            ->live()
                            ->nullable(),
                        Select::make('category_id')
                            ->label('Категория')
                            ->relationship('category', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('product_target_type') === PromotionProductTargetType::Category->value
                                || blank($get('product_target_type')))
                            ->nullable(),
                        Select::make('catalog_id')
                            ->label('Каталог (коллекция)')
                            ->relationship('catalog', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Catalog $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('product_target_type') === PromotionProductTargetType::Catalog->value)
                            ->nullable(),
                        Select::make('products')
                            ->label('Товары')
                            ->relationship('products', 'sku')
                            ->getOptionLabelFromRecordUsing(
                                fn (Product $record) => ($record->translate('name', 'pl') ?? $record->sku).' ('.$record->sku.')'
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('product_target_type') === PromotionProductTargetType::Products->value)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                        Toggle::make('show_on_homepage')
                            ->label('На главной')
                            ->default(false),
                        DateTimePicker::make('starts_at')
                            ->label('Начало')
                            ->seconds(false),
                        DateTimePicker::make('expires_at')
                            ->label('Окончание')
                            ->seconds(false),
                        FileUpload::make('image_path')
                            ->label('Изображение')
                            ->image()
                            ->directory('promotions')
                            ->columnSpanFull(),
                        TextInput::make('link_url')
                            ->label('Ссылка (необязательно)')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://…')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                TranslationTabs::make('promotion'),
            ]);
    }
}
