<?php

namespace App\Filament\Resources\Homepage\Schemas;

use App\Enums\HomepageBlockType;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Language;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class HomepageSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Конструктор главной страницы')
                ->description('Добавляйте блоки, меняйте порядок перетаскиванием. Контент hero и баннеров редактируется в соответствующих разделах каталога.')
                ->schema([
                    Repeater::make('blocks')
                        ->label('Блоки')
                        ->schema(static::blockSchema())
                        ->default(static::defaultBlocks())
                        ->itemLabel(fn (array $state): string => static::blockLabel($state))
                        ->addActionLabel('Добавить блок')
                        ->reorderable()
                        ->reorderableWithDragAndDrop()
                        ->collapsible()
                        ->cloneable()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return list<\Filament\Forms\Components\Component|\Filament\Schemas\Components\Component>
     */
    protected static function blockSchema(): array
    {
        return [
            Select::make('type')
                ->label('Тип блока')
                ->options(collect(HomepageBlockType::cases())->mapWithKeys(
                    fn (HomepageBlockType $type) => [$type->value => $type->label()]
                ))
                ->required()
                ->native(false)
                ->live()
                ->helperText(fn (Get $get): ?string => ($type = static::typeFromGet($get))
                    ? $type->description()
                    : null),
            Toggle::make('is_active')
                ->label('Показывать на сайте')
                ->default(true),
            Placeholder::make('hero_hint')
                ->hiddenLabel()
                ->content('Слайды и раскладка hero — в разделе «Hero-баннеры».')
                ->visible(fn (Get $get): bool => static::typeFromGet($get) === HomepageBlockType::Hero),
            Placeholder::make('banners_hint')
                ->hiddenLabel()
                ->content('Изображения баннеров — в разделе «Баннеры».')
                ->visible(fn (Get $get): bool => static::typeFromGet($get) === HomepageBlockType::Banners),
            ...static::titleFields(),
            Section::make('Категории в ленте')
                ->schema([
                    Repeater::make('settings.items')
                        ->label('Категории')
                        ->schema([
                            Select::make('category_id')
                                ->label('Категория')
                                ->options(static::categoryOptions())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false),
                            Select::make('sublabel_key')
                                ->label('Подпись')
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
                        ->collapsible(),
                ])
                ->visible(fn (Get $get): bool => static::typeFromGet($get) === HomepageBlockType::CategoryShowcase)
                ->columnSpanFull(),
            Section::make('Каталог товаров')
                ->schema([
                    Select::make('settings.catalog_id')
                        ->label('Каталог')
                        ->options(static::catalogOptions())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),
                    TextInput::make('settings.limit')
                        ->label('Количество товаров')
                        ->numeric()
                        ->default(12)
                        ->minValue(4)
                        ->maxValue(24),
                    Select::make('settings.layout')
                        ->label('Вид карточек')
                        ->options([
                            'trending' => 'Стандартные (как в трендах)',
                            'new_arrivals' => 'Компактные (как в новинках)',
                        ])
                        ->default('trending')
                        ->native(false),
                ])
                ->columns(3)
                ->visible(fn (Get $get): bool => static::typeFromGet($get) === HomepageBlockType::Catalog)
                ->columnSpanFull(),
            Select::make('settings.size')
                ->label('Высота отступа')
                ->options([
                    'sm' => 'Маленький',
                    'md' => 'Средний',
                    'lg' => 'Большой',
                    'xl' => 'Очень большой',
                ])
                ->default('md')
                ->native(false)
                ->visible(fn (Get $get): bool => static::typeFromGet($get) === HomepageBlockType::Spacer),
        ];
    }

    /**
     * @return list<Section>
     */
    protected static function titleFields(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Language $language) => Section::make('Заголовок ('.strtoupper($language->code).')')
                ->schema([
                    TextInput::make("settings.title.{$language->code}")
                        ->label('Заголовок')
                        ->maxLength(120)
                        ->placeholder('Оставьте пустым для стандартного'),
                ])
                ->visible(fn (Get $get): bool => in_array(static::typeFromGet($get), [
                    HomepageBlockType::CategoryShowcase,
                    HomepageBlockType::Trending,
                    HomepageBlockType::Promotions,
                    HomepageBlockType::NewArrivals,
                    HomepageBlockType::Catalog,
                ], true))
                ->compact()
                ->columnSpanFull())
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function defaultBlocks(): array
    {
        return \App\Models\HomepageSettings::defaultBlocks();
    }

  /**
     * @param  array<string, mixed>  $state
     */
    protected static function blockLabel(array $state): string
    {
        $type = HomepageBlockType::tryFrom((string) ($state['type'] ?? ''));

        if (! $type) {
            return 'Блок';
        }

        $label = $type->label();

        if (! ($state['is_active'] ?? true)) {
            return "{$label} (скрыт)";
        }

        if ($type === HomepageBlockType::Catalog && filled($state['settings']['catalog_id'] ?? null)) {
            $catalog = Catalog::query()->find($state['settings']['catalog_id']);
            $name = $catalog?->translate('name', 'pl') ?? $catalog?->code;

            if ($name) {
                return "{$label}: {$name}";
            }
        }

        return $label;
    }

    protected static function typeFromGet(Get $get): ?HomepageBlockType
    {
        return HomepageBlockType::tryFrom((string) $get('type'));
    }

    /**
     * @return array<int, string>
     */
    protected static function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->id => ($category->translate('name', 'pl') ?? $category->code).' ('.$category->code.')',
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected static function catalogOptions(): array
    {
        return Catalog::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Catalog $catalog) => [
                $catalog->id => ($catalog->translate('name', 'pl') ?? $catalog->code).' ('.$catalog->code.')',
            ])
            ->all();
    }
}
