<?php

namespace App\Filament\Resources\NavMenus\Schemas;

use App\Enums\NavPanelType;
use App\Filament\Forms\NavItemLabelFields;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\NavItem;
use App\Models\NavMenu;
use App\Models\Product;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NavMenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Меню')
                    ->description('Мега-меню: в колонке можно выбрать несколько категорий или каталогов — каждый станет отдельной ссылкой.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Название (админка)')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(64)
                            ->helperText('header — шапка, footer — колонки футера (редактируются в «Футер — колонки»)'),
                        Toggle::make('is_active')
                            ->label('Активно')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Пункты навигации')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->schema(static::itemSchema(depth: 0))
                            ->defaultItems(0)
                            ->addActionLabel('Добавить пункт')
                            ->reorderable()
                            ->orderColumn('sort_order')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => static::itemPreviewLabel($state))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return list<Component>
     */
    protected static function itemSchema(int $depth): array
    {
        $schema = [
            ...NavItemLabelFields::make(),
            TextInput::make('url')
                ->label('Ссылка пункта')
                ->maxLength(500)
                ->placeholder('Необязательно для мега-меню'),
            Toggle::make('open_in_new_tab')
                ->label('Новая вкладка')
                ->default(false),
            Toggle::make('is_active')
                ->label('Активен')
                ->default(true),
        ];

        if ($depth < 1) {
            $schema[] = Repeater::make('panels')
                ->label('Колонки мега-меню')
                ->relationship('panels')
                ->schema(static::panelSchema())
                ->defaultItems(0)
                ->addActionLabel('Добавить колонку')
                ->reorderable()
                ->orderColumn('sort_order')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => static::panelPreviewLabel($state))
                ->visible(fn (Get $get, $record = null) => static::isHeaderMenu($get, $record))
                ->columnSpanFull();

            $schema[] = Repeater::make('children')
                ->label('Простой выпадающий список')
                ->relationship('children')
                ->schema(static::itemSchema(depth: 1))
                ->defaultItems(0)
                ->addActionLabel('Добавить ссылку')
                ->reorderable()
                ->orderColumn('sort_order')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => static::itemPreviewLabel($state))
                ->helperText('Только если колонки мега-меню не используются.')
                ->visible(fn (Get $get, $record = null) => static::isHeaderMenu($get, $record))
                ->columnSpanFull();
        }

        return $schema;
    }

    /**
     * @return list<Component>
     */
    protected static function panelSchema(): array
    {
        return [
            Select::make('type')
                ->label('Тип колонки')
                ->options(collect(NavPanelType::cases())->mapWithKeys(
                    fn (NavPanelType $type) => [$type->value => $type->label()]
                ))
                ->default(NavPanelType::Category->value)
                ->required()
                ->native(false)
                ->live(),
            ...array_map(
                fn (TextInput $field) => $field
                    ->helperText('Необязательно — для категории подставится название из каталога.'),
                NavItemLabelFields::make('title_labels'),
            ),
            Select::make('categories')
                ->label('Категории')
                ->relationship(
                    name: 'categories',
                    titleAttribute: 'code',
                    modifyQueryUsing: fn ($query) => $query->where('is_active', true),
                )
                ->getOptionLabelFromRecordUsing(
                    fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                )
                ->multiple()
                ->searchable()
                ->preload()
                ->required(fn (Get $get) => $get('type') === NavPanelType::Category->value)
                ->helperText('Несколько категорий — отдельные ссылки (обувь, одежда, сумки…). Одна категория + «Подкатегории» — список дочерних разделов.')
                ->visible(fn (Get $get) => $get('type') === NavPanelType::Category->value)
                ->columnSpanFull(),
            Toggle::make('show_subcategories')
                ->label('Показать подкатегории')
                ->default(true)
                ->helperText('Только если выбрана одна категория.')
                ->visible(fn (Get $get) => $get('type') === NavPanelType::Category->value),
            Toggle::make('show_products')
                ->label('Показать товары категории')
                ->default(false)
                ->live()
                ->visible(fn (Get $get) => $get('type') === NavPanelType::Category->value),
            Select::make('products')
                ->label('Товары')
                ->relationship(
                    name: 'products',
                    titleAttribute: 'sku',
                    modifyQueryUsing: fn ($query) => $query->published(),
                )
                ->getOptionLabelFromRecordUsing(
                    fn (Product $record) => ($record->translate('name', 'pl') ?? $record->sku).' ('.$record->sku.')'
                )
                ->multiple()
                ->searchable()
                ->preload()
                ->required(fn (Get $get) => $get('type') === NavPanelType::SelectedProducts->value)
                ->visible(fn (Get $get) => $get('type') === NavPanelType::SelectedProducts->value)
                ->columnSpanFull(),
            Select::make('catalogs')
                ->label('Каталоги (коллекции)')
                ->relationship(
                    name: 'catalogs',
                    titleAttribute: 'code',
                    modifyQueryUsing: fn ($query) => $query->where('is_active', true),
                )
                ->getOptionLabelFromRecordUsing(
                    fn (Catalog $record) => $record->translate('name', 'pl') ?? $record->code
                )
                ->multiple()
                ->searchable()
                ->preload()
                ->required(fn (Get $get) => $get('type') === NavPanelType::CatalogProducts->value)
                ->helperText('Несколько каталогов — ссылки на страницы коллекций. Один каталог — превью товаров в колонке.')
                ->visible(fn (Get $get) => $get('type') === NavPanelType::CatalogProducts->value)
                ->columnSpanFull(),
            TextInput::make('columns')
                ->label('Колонок текста')
                ->numeric()
                ->minValue(1)
                ->maxValue(2)
                ->default(1)
                ->visible(fn (Get $get) => in_array($get('type'), [
                    NavPanelType::Category->value,
                    NavPanelType::Brands->value,
                    NavPanelType::Links->value,
                ], true)),
            TextInput::make('item_limit')
                ->label('Лимит')
                ->numeric()
                ->minValue(1)
                ->maxValue(48)
                ->default(12)
                ->helperText(fn (Get $get) => match ($get('type')) {
                    NavPanelType::SelectedProducts->value => 'Не используется — показываются все выбранные товары.',
                    NavPanelType::Category->value => $get('show_products')
                        ? 'Макс. подкатегорий и товаров (одна категория).'
                        : 'Макс. ссылок в колонке (категории, подкатегории или каталоги).',
                    NavPanelType::CatalogProducts->value => 'Макс. ссылок при нескольких каталогах; при одном — число товаров в превью.',
                    default => 'Макс. элементов в колонке.',
                }),
            Toggle::make('is_active')
                ->label('Активна')
                ->default(true),
            Repeater::make('links')
                ->label('Ссылки вручную')
                ->relationship('links')
                ->schema([
                    ...NavItemLabelFields::make(),
                    TextInput::make('url')
                        ->label('Ссылка')
                        ->maxLength(500)
                        ->required(),
                    Toggle::make('open_in_new_tab')
                        ->label('Новая вкладка')
                        ->default(false),
                ])
                ->defaultItems(0)
                ->addActionLabel('Добавить ссылку')
                ->reorderable()
                ->orderColumn('sort_order')
                ->collapsible()
                ->visible(fn (Get $get) => $get('type') === NavPanelType::Links->value)
                ->columnSpanFull(),
        ];
    }

    protected static function itemPreviewLabel(array $state): ?string
    {
        $labels = $state['labels'] ?? [];
        $default = (string) config('shop.default_language', 'pl');

        return $labels[$default]
            ?? ($labels ? (string) reset($labels) : null)
            ?? 'Пункт меню';
    }

    protected static function panelPreviewLabel(array $state): ?string
    {
        $type = NavPanelType::tryFrom((string) ($state['type'] ?? ''));
        $title = static::itemPreviewLabel(['labels' => $state['title_labels'] ?? []]);

        return $title ?? $type?->label() ?? 'Колонка';
    }

    protected static function isHeaderMenu(Get $get, mixed $record = null): bool
    {
        return static::menuCode($get, $record) !== 'footer';
    }

    protected static function menuCode(Get $get, mixed $record = null): ?string
    {
        if ($record instanceof NavMenu) {
            return $record->code;
        }

        if ($record instanceof NavItem) {
            return $record->relationLoaded('menu')
                ? $record->menu?->code
                : NavMenu::query()->whereKey($record->nav_menu_id)->value('code');
        }

        return $get('code');
    }
}
