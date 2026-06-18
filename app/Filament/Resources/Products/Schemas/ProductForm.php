<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Filament\Forms\TranslationTabs;
use App\Filament\Support\FilamentMedia;
use App\Filament\Support\ProductSizeChartPresetOptions;
use App\Filament\Support\ProductSizeGridOptions;
use App\Models\Brand;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
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
use App\Support\Shop\ProductSkuGenerator;

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
                            ->label('Пресет размеров')
                            ->schema(static::sizeGridTabSchema()),
                        Tab::make('size_chart_preset')
                            ->label('Таблица мерок')
                            ->schema(static::sizeChartPresetTabSchema()),
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
            Section::make('Как заполнять товар')
                ->description('Краткая инструкция — подробности в подсказках под каждым полем.')
                ->schema([
                    Placeholder::make('product_guide')
                        ->hiddenLabel()
                        ->content(implode("\n", [
                            '1. Название (PL) — обязательно. Остальные языки можно добавить позже.',
                            '2. SKU — артикул товара. Можно включить «Сгенерировать автоматически».',
                            '3. Категория и бренд — чтобы товар попал в нужный раздел каталога.',
                            '4. Цена — базовая цена; для размеров уточните на вкладке «Размеры».',
                            '5. model_slug — только если это тот же товар в другом цвете. Иначе оставьте пустым!',
                            '6. Пресет размеров (вкладка) — кнопки S/M/L. Для сумок: bags_sml или accessories_one_size.',
                            '7. Фото — главное здесь; галерея на вкладке «Изображения».',
                        ]))
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed()
                ->columnSpanFull(),
            Section::make('Каталог и модель')
                ->schema([
                    Select::make('catalogs')
                        ->label('Каталоги')
                        ->helperText('Витрины на сайте (главная, trendy, новинки…). Можно выбрать несколько.')
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
                        ->helperText('Главный раздел: женское, мужское, аксессуары… От него зависит навигация.')
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
                        ->helperText('Только для цветовых вариантов ОДНОЙ модели (платье в чёрном и бежевом). Для разных товаров — не заполняйте!'),
                    Toggle::make('create_new_color')
                        ->label('Создать новый цвет')
                        ->helperText('Или выберите существующий цвет из справочника «Цвета».')
                        ->default(false)
                        ->live()
                        ->columnSpanFull(),
                    Select::make('color_id')
                        ->label('Цвет из справочника')
                        ->relationship(
                            name: 'color',
                            titleAttribute: 'code',
                            modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Color $record) => ($record->translate('name', 'pl') ?? $record->code).' ('.$record->code.')'
                        )
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->visible(fn (Get $get): bool => ! (bool) $get('create_new_color')),
                    TextInput::make('new_color_name_pl')
                        ->label('Название цвета (PL)')
                        ->helperText('Будет добавлен в справочник с автопереводом на английский.')
                        ->maxLength(64)
                        ->visible(fn (Get $get): bool => (bool) $get('create_new_color')),
                    ColorPicker::make('new_color_hex')
                        ->label('HEX нового цвета')
                        ->visible(fn (Get $get): bool => (bool) $get('create_new_color')),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Цены и идентификация')
                ->schema([
                    Toggle::make('auto_generate_sku')
                        ->label('Сгенерировать SKU автоматически')
                        ->helperText('Уникальный артикул из названия товара (PL). Можно оставить включённым для новых товаров.')
                        ->default(fn (?Product $record): bool => $record === null || ProductSkuGenerator::isDraftSku($record->sku))
                        ->live()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    TextInput::make('sku')
                        ->label('SKU (артикул)')
                        ->required(fn (Get $get): bool => ! $get('auto_generate_sku'))
                        ->disabled(fn (Get $get): bool => (bool) $get('auto_generate_sku'))
                        ->unique(ignoreRecord: true)
                        ->maxLength(64)
                        ->helperText('Уникальный код товара для склада и импорта. Пример: DRESS-001-BLACK'),
                    Select::make('status')
                        ->label('Статус')
                        ->options(collect(ProductStatus::cases())->mapWithKeys(
                            fn (ProductStatus $status) => [$status->value => $status->label()]
                        ))
                        ->required()
                        ->native(false),
                    Select::make('type')
                        ->label('Тип товара')
                        ->helperText('С размерами (одежда, обувь) — variable. Один размер (сумка, аксессуар) — simple.')
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
                        ->helperText('Цена в PLN. Для товаров с размерами можно задать отдельно на вкладке «Размеры».')
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
            Section::make('Пресет размеров для кнопок на сайте')
                ->description('Кнопки размера на карточке товара (S, M, L, 25 см…). Это НЕ таблица мерок в сантиметрах — она на соседней вкладке. Справочник: Каталог → Размеры (кнопки).')
                ->schema([
                    Select::make('size_grid_id')
                        ->label('Пресет')
                        ->options(fn (Get $get): array => ProductSizeGridOptions::presets(
                            null,
                            $get('size_grid_id') ? (int) $get('size_grid_id') : null,
                        ))
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->live()
                        ->nullable()
                        ->helperText('Одежда: clothing_letter_women. Обувь: footwear_eu. Сумки: bags_sml или accessories_one_size. После выбора — вкладка «Размеры».'),
                    Placeholder::make('size_grid_sizes_preview')
                        ->label('Размеры в пресете')
                        ->content(function (Get $get): string {
                            $labels = ProductSizeGridOptions::sizeLabels(
                                $get('size_grid_id') ? (int) $get('size_grid_id') : null,
                            );

                            return $labels === [] ? '—' : implode(' · ', $labels);
                        })
                        ->visible(fn (Get $get): bool => filled($get('size_grid_id'))),
                    Placeholder::make('size_grid_hint')
                        ->hiddenLabel()
                        ->content('Варианты с ценами добавляются на вкладке «Размеры» рядом. Таблица мерок (грудь, талия…) — вкладка «Таблица мерок».'),
                ])
                ->columnSpanFull(),
        ];
    }

    /** @return list<\Filament\Schemas\Components\Component> */
    protected static function sizeChartPresetTabSchema(): array
    {
        return [
            Section::make('Пресет таблицы мерок (визуальная сетка)')
                ->description('Точные мерки в сантиметрах (грудь, талия…) — для одежды. Для сумок обычно не нужно. Кнопки S/M/L — вкладка «Пресет размеров».')
                ->schema([
                    Select::make('size_chart_preset_id')
                        ->label('Пресет таблицы')
                        ->options(fn (Get $get): array => ProductSizeChartPresetOptions::presets(
                            $get('size_chart_preset_id') ? (int) $get('size_chart_preset_id') : null,
                        ))
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->live()
                        ->nullable()
                        ->helperText('После выбора сохраните товар. Таблица появится на сайте. Свои правки — вкладка «Таблица мерок» у товара.'),
                    Placeholder::make('size_chart_preset_preview')
                        ->label('Превью')
                        ->content(function (Get $get): string {
                            $presetId = $get('size_chart_preset_id') ? (int) $get('size_chart_preset_id') : null;

                            if (! $presetId) {
                                return '—';
                            }

                            $rows = ProductSizeChartPresetOptions::rowsForProductCopy($presetId);

                            if ($rows === []) {
                                return 'В пресете нет строк.';
                            }

                            return collect($rows)
                                ->map(fn (array $row) => sprintf(
                                    '%s: klatka %s, talia %s',
                                    $row['size'],
                                    $row['chest'] ?? '—',
                                    $row['waist'] ?? '—',
                                ))
                                ->implode("\n");
                        })
                        ->visible(fn (Get $get): bool => filled($get('size_chart_preset_id'))),
                    Placeholder::make('size_chart_preset_hint')
                        ->hiddenLabel()
                        ->content('Чтобы скопировать пресет в товар для ручной правки: вкладка «Таблица мерок» → «Применить пресет таблицы мерок».'),
                ])
                ->columnSpanFull(),
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
