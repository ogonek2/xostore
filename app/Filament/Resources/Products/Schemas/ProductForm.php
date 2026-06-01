<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Filament\Forms\TranslationTabs;
use App\Filament\Support\FilamentMedia;
use App\Models\Brand;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\SizeGrid;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('productTabs')
                    ->tabs([
                        Tab::make('general')
                            ->label('Основное')
                            ->schema(static::generalTabSchema()),
                        Tab::make('fit')
                            ->label('Посадка и ткань')
                            ->schema([
                                TranslationTabs::make('product', 'Переводы', [
                                    'fit_description',
                                    'fabric_description',
                                ]),
                            ]),
                        Tab::make('size_grid')
                            ->label('Размерная сетка')
                            ->schema(static::sizeGridTabSchema()),
                        Tab::make('seo')
                            ->label('SEO')
                            ->schema([
                                TranslationTabs::make('product', 'Переводы', [
                                    'meta_title',
                                    'meta_description',
                                ]),
                            ]),
                        Tab::make('tailoring')
                            ->label('Индивидуальный пошив')
                            ->schema(static::tailoringTabSchema()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /** @return list<\Filament\Schemas\Components\Component> */
    protected static function generalTabSchema(): array
    {
        return [
            Section::make('Каталог и модель')
                ->schema([
                    Select::make('catalogs')
                        ->label('Каталоги')
                        ->relationship('catalogs', 'code')
                        ->getOptionLabelFromRecordUsing(
                            fn (Catalog $record) => $record->translate('name', 'pl') ?? $record->code
                        )
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->columnSpanFull(),
                    Select::make('primary_category_id')
                        ->label('Основная категория')
                        ->relationship('primaryCategory', 'code')
                        ->getOptionLabelFromRecordUsing(
                            fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                        )
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live(),
                    TextInput::make('model_slug')
                        ->label('Модель (slug)')
                        ->maxLength(128)
                        ->live(onBlur: true)
                        ->helperText('Один slug для всех цветов одной модели (напр. celestia)'),
                    TextInput::make('color_label')
                        ->label('Цвет')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            $existing = $get('color_slug');

                            if (is_string($existing) && trim($existing) !== '') {
                                return;
                            }

                            if (! is_string($state) || trim($state) === '') {
                                return;
                            }

                            $set('color_slug', Str::slug($state));
                        })
                        ->maxLength(64),
                    TextInput::make('color_slug')
                        ->label('Slug цвета')
                        ->maxLength(64),
                    ColorPicker::make('color_hex')
                        ->label('HEX цвета')
                        ->helperText('Значение сохраняется как HEX'),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Цены и склад')
                ->schema([
                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(64),
                    Select::make('status')
                        ->label('Статус')
                        ->options(collect(ProductStatus::cases())->mapWithKeys(
                            fn (ProductStatus $status) => [$status->value => $status->label()]
                        ))
                        ->required()
                        ->native(false),
                    Select::make('type')
                        ->label('Тип товара')
                        ->options(collect(ProductType::cases())->mapWithKeys(
                            fn (ProductType $type) => [$type->value => $type->label()]
                        ))
                        ->required()
                        ->native(false),
                    Select::make('brand_id')
                        ->label('Бренд')
                        ->relationship('brand', 'code')
                        ->getOptionLabelFromRecordUsing(
                            fn (Brand $record) => $record->translate('name', 'pl') ?? $record->code
                        )
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    TextInput::make('base_price')
                        ->label('Цена')
                        ->numeric()
                        ->prefix('PLN')
                        ->minValue(0)
                        ->required(),
                    TextInput::make('compare_at_price')
                        ->label('Старая цена')
                        ->numeric()
                        ->prefix('PLN')
                        ->minValue(0),
                    TextInput::make('weight_grams')
                        ->label('Вес (г)')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('sort_order')
                        ->label('Сортировка')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Связи и публикация')
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
                        ->columnSpanFull(),
                    Toggle::make('is_featured')
                        ->label('Рекомендованный'),
                    Toggle::make('is_new')
                        ->label('Новинка'),
                    Toggle::make('is_ready_to_ship')
                        ->label('Добавить в товары в наличии')
                        ->helperText('Товар появится во вкладке «Товары в наличии» на сайте — можно купить и отправить сразу. Остальные товары — предзаказ.'),
                    DateTimePicker::make('published_at')
                        ->label('Дата публикации')
                        ->seconds(false),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Главное фото')
                ->schema([
                    FilamentMedia::image('primary_image', 'products')
                        ->label('Главное фото')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            TranslationTabs::make('product', 'Переводы', [
                'name',
                'slug',
                'subtitle',
                'short_description',
                'description',
            ]),
        ];
    }

    /** @return list<\Filament\Schemas\Components\Component> */
    protected static function sizeGridTabSchema(): array
    {
        return [
            Select::make('size_grid_id')
                ->label('Пресет размерной сетки')
                ->options(fn () => SizeGrid::query()
                    ->where('is_active', true)
                    ->get()
                    ->mapWithKeys(fn (SizeGrid $grid) => [
                        $grid->id => $grid->translate('name', 'pl') ?? $grid->code,
                    ]))
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Пресет для вариантов. Таблица мерок — на вкладке «Размерная сетка» товара.'),
        ];
    }

    /** @return list<\Filament\Schemas\Components\Component> */
    protected static function tailoringTabSchema(): array
    {
        return [
            Toggle::make('custom_tailoring_enabled')
                ->label('Доступен индивидуальный пошив')
                ->live(),
            TranslationTabs::make('product', 'Описание пошива', [
                'tailoring_description',
            ])
                ->visible(fn (Get $get) => (bool) $get('custom_tailoring_enabled')),
        ];
    }
}
