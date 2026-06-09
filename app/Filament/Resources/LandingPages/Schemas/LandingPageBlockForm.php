<?php

namespace App\Filament\Resources\LandingPages\Schemas;

use App\Enums\LandingPageBlockType;
use App\Filament\Forms\TranslationTabs;
use App\Filament\Support\FilamentMedia;
use App\Models\Language;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class LandingPageBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Тип блока')
                ->schema([
                    Select::make('type')
                        ->label('Тип')
                        ->options(collect(LandingPageBlockType::cases())->mapWithKeys(
                            fn (LandingPageBlockType $type) => [$type->value => $type->label()]
                        ))
                        ->required()
                        ->native(false)
                        ->live(),
                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ])
                ->columns(3)
                ->columnSpanFull(),
            ...static::translationSection(),
            ...static::settingsSections(),
        ]);
    }

    /**
     * @return list<Section>
     */
    protected static function translationSection(): array
    {
        return [
            Section::make('Тексты (переводы)')
                ->schema([
                    TranslationTabs::make('landing_page_block', 'Контент блока', static::translationFields()),
                ])
                ->visible(fn (Get $get): bool => static::typeFromGet($get) !== LandingPageBlockType::Spacer)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<string>
     */
    public static function translationFields(): array
    {
        return ['title', 'subtitle', 'content', 'button_label', 'link_url', 'caption'];
    }

    /**
     * @return list<Section>
     */
    protected static function settingsSections(): array
    {
        return [
            static::layoutSettings(),
            static::heroSettings(),
            static::headingSettings(),
            static::richTextSettings(),
            static::textImageSettings(),
            static::mediaSettings(),
            static::ctaSettings(),
            static::gallerySettings(),
            static::faqSettings(),
            static::featuresSettings(),
            static::productsSettings(),
            static::spacerSettings(),
        ];
    }

    /**
     * @return list<LandingPageBlockType>
     */
    protected static function layoutBlockTypes(): array
    {
        return [
            LandingPageBlockType::Heading,
            LandingPageBlockType::RichText,
            LandingPageBlockType::TextImage,
            LandingPageBlockType::Media,
            LandingPageBlockType::Gallery,
            LandingPageBlockType::Faq,
            LandingPageBlockType::Features,
            LandingPageBlockType::Products,
        ];
    }

    protected static function layoutSettings(): Section
    {
        return Section::make('Оформление секции')
            ->schema([
                Select::make('settings.theme')
                    ->label('Тема')
                    ->options([
                        'light' => 'Светлая (как новинки на главной)',
                        'dark' => 'Тёмная (как акции на главной)',
                    ])
                    ->default('light')
                    ->native(false),
                Select::make('settings.align')
                    ->label('Выравнивание')
                    ->options([
                        'left' => 'Слева',
                        'center' => 'По центру',
                        'right' => 'Справа',
                    ])
                    ->default('center')
                    ->native(false),
            ])
            ->columns(2)
            ->visible(fn (Get $get): bool => in_array(static::typeFromGet($get), static::layoutBlockTypes(), true))
            ->columnSpanFull();
    }

    protected static function heroSettings(): Section
    {
        return Section::make('Hero — оформление')
            ->schema([
                FilamentMedia::image('settings.image_path', 'landing-pages')
                    ->label('Фоновое изображение')
                    ->columnSpanFull(),
                Select::make('settings.text_align')
                    ->label('Выравнивание')
                    ->options([
                        'left' => 'Слева',
                        'center' => 'По центру',
                        'right' => 'Справа',
                    ])
                    ->default('center'),
                Select::make('settings.height')
                    ->label('Высота')
                    ->options([
                        'sm' => 'Компактная',
                        'md' => 'Средняя',
                        'lg' => 'Высокая',
                        'full' => 'На весь экран',
                    ])
                    ->default('lg'),
                TextInput::make('settings.overlay_opacity')
                    ->label('Затемнение (0–80)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(80)
                    ->default(35),
                Select::make('settings.text_color')
                    ->label('Цвет текста')
                    ->options(['light' => 'Светлый', 'dark' => 'Тёмный'])
                    ->default('light'),
            ])
            ->columns(2)
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Hero)
            ->columnSpanFull();
    }

    protected static function headingSettings(): Section
    {
        return Section::make('Заголовок — оформление')
            ->schema([
                Select::make('settings.level')
                    ->label('Уровень')
                    ->options(['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3'])
                    ->default('h2'),
                Select::make('settings.style')
                    ->label('Стиль')
                    ->options([
                        'default' => 'Обычный',
                        'display' => 'Крупный (display)',
                        'eyebrow' => 'С меткой над заголовком',
                    ])
                    ->default('default'),
            ])
            ->columns(3)
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Heading)
            ->columnSpanFull();
    }

    protected static function richTextSettings(): Section
    {
        return Section::make('Текст — оформление')
            ->schema([
                Select::make('settings.width')
                    ->label('Ширина контента')
                    ->options([
                        'narrow' => 'Узкая',
                        'default' => 'Стандарт',
                        'wide' => 'Широкая',
                    ])
                    ->default('default'),
                Select::make('settings.padding')
                    ->label('Отступы секции')
                    ->options(['sm' => 'Малые', 'md' => 'Средние', 'lg' => 'Большие'])
                    ->default('md'),
            ])
            ->columns(2)
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::RichText)
            ->columnSpanFull();
    }

    protected static function textImageSettings(): Section
    {
        return Section::make('Текст + картинка')
            ->schema([
                FilamentMedia::image('settings.image_path', 'landing-pages')
                    ->label('Изображение')
                    ->required()
                    ->columnSpanFull(),
                Select::make('settings.image_position')
                    ->label('Расположение')
                    ->options([
                        'left' => 'Картинка слева, текст справа',
                        'right' => 'Текст слева, картинка справа',
                    ])
                    ->default('right')
                    ->native(false),
                Select::make('settings.image_aspect')
                    ->label('Соотношение сторон')
                    ->options([
                        '4/5' => '4:5 (портрет)',
                        '1/1' => '1:1',
                        '4/3' => '4:3',
                        '16/9' => '16:9',
                    ])
                    ->default('4/5')
                    ->native(false),
            ])
            ->columns(2)
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::TextImage)
            ->columnSpanFull();
    }

    protected static function mediaSettings(): Section
    {
        return Section::make('Медиа')
            ->schema([
                FilamentMedia::image('settings.image_path', 'landing-pages')
                    ->label('Изображение'),
                TextInput::make('settings.video_url')
                    ->label('Видео URL (YouTube / Vimeo / mp4)')
                    ->url()
                    ->maxLength(500),
                Select::make('settings.aspect')
                    ->label('Соотношение сторон')
                    ->options([
                        'auto' => 'Авто',
                        '16/9' => '16:9',
                        '4/3' => '4:3',
                        '1/1' => '1:1',
                        '21/9' => '21:9',
                    ])
                    ->default('16/9'),
                Select::make('settings.width')
                    ->label('Ширина')
                    ->options(['full' => 'На всю ширину', 'contained' => 'В контейнере'])
                    ->default('contained'),
            ])
            ->columns(2)
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Media)
            ->columnSpanFull();
    }

    protected static function ctaSettings(): Section
    {
        return Section::make('CTA — оформление')
            ->schema([
                Select::make('settings.style')
                    ->label('Стиль')
                    ->options([
                        'primary' => 'Тёмный',
                        'light' => 'Светлый',
                        'outline' => 'Контур',
                        'image' => 'С фоновым изображением',
                    ])
                    ->default('primary')
                    ->live(),
                FilamentMedia::image('settings.image_path', 'landing-pages')
                    ->label('Фоновое изображение')
                    ->visible(fn (Get $get): bool => ($get('settings.style') ?? '') === 'image'),
                Select::make('settings.align')
                    ->label('Выравнивание')
                    ->options(['left' => 'Слева', 'center' => 'По центру'])
                    ->default('center'),
            ])
            ->columns(2)
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Cta)
            ->columnSpanFull();
    }

    protected static function gallerySettings(): Section
    {
        return Section::make('Галерея — элементы')
            ->schema([
                Select::make('settings.columns')
                    ->label('Колонки')
                    ->options(['2' => '2', '3' => '3', '4' => '4'])
                    ->default('3'),
                Select::make('settings.gap')
                    ->label('Отступ между фото')
                    ->options(['sm' => 'Малый', 'md' => 'Средний', 'lg' => 'Большой'])
                    ->default('md'),
                static::localizedItemsRepeater(
                    type: LandingPageBlockType::Gallery,
                    withImage: true,
                    withLink: true,
                ),
            ])
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Gallery)
            ->columnSpanFull();
    }

    protected static function faqSettings(): Section
    {
        return Section::make('FAQ — вопросы')
            ->schema([
                Select::make('settings.style')
                    ->label('Стиль')
                    ->options(['accordion' => 'Аккордеон', 'cards' => 'Карточки'])
                    ->default('accordion'),
                static::localizedItemsRepeater(
                    type: LandingPageBlockType::Faq,
                    withRichAnswer: true,
                ),
            ])
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Faq)
            ->columnSpanFull();
    }

    protected static function featuresSettings(): Section
    {
        return Section::make('Карточки преимуществ')
            ->schema([
                Select::make('settings.columns')
                    ->label('Колонки')
                    ->options(['2' => '2', '3' => '3', '4' => '4'])
                    ->default('3'),
                static::localizedItemsRepeater(
                    type: LandingPageBlockType::Features,
                    withIcon: true,
                    withSubtitle: true,
                ),
            ])
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Features)
            ->columnSpanFull();
    }

    protected static function productsSettings(): Section
    {
        return Section::make('Товары — источник')
            ->schema([
                Select::make('settings.source')
                    ->label('Источник')
                    ->options([
                        'trending' => 'Популярные (каталог trendy)',
                        'new_arrivals' => 'Новинки',
                        'catalog' => 'Каталог (код)',
                        'category' => 'Категория (код)',
                    ])
                    ->default('trending')
                    ->live()
                    ->native(false),
                TextInput::make('settings.catalog_code')
                    ->label('Код каталога')
                    ->visible(fn (Get $get): bool => ($get('settings.source') ?? '') === 'catalog'),
                TextInput::make('settings.category_code')
                    ->label('Код категории')
                    ->visible(fn (Get $get): bool => ($get('settings.source') ?? '') === 'category'),
                TextInput::make('settings.limit')
                    ->label('Количество')
                    ->numeric()
                    ->default(8)
                    ->minValue(1)
                    ->maxValue(24),
            ])
            ->columns(2)
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Products)
            ->columnSpanFull();
    }

    protected static function spacerSettings(): Section
    {
        return Section::make('Отступ')
            ->schema([
                Select::make('settings.size')
                    ->label('Размер')
                    ->options([
                        'xs' => 'XS',
                        'sm' => 'S',
                        'md' => 'M',
                        'lg' => 'L',
                        'xl' => 'XL',
                    ])
                    ->default('md'),
            ])
            ->visible(fn (Get $get): bool => static::typeFromGet($get) === LandingPageBlockType::Spacer)
            ->columnSpanFull();
    }

    protected static function localizedItemsRepeater(
        LandingPageBlockType $type,
        bool $withImage = false,
        bool $withLink = false,
        bool $withRichAnswer = false,
        bool $withIcon = false,
        bool $withSubtitle = false,
    ): Repeater {
        $languages = Language::query()->where('is_active', true)->orderBy('sort_order')->get();
        $schema = [];

        if ($withImage) {
            $schema[] = FilamentMedia::image('image_path', 'landing-pages')
                ->label('Изображение')
                ->required();
        }

        if ($withIcon) {
            $schema[] = TextInput::make('icon')
                ->label('Иконка (emoji или символ)')
                ->maxLength(8)
                ->placeholder('✦');
            $schema[] = FilamentMedia::image('image_path', 'landing-pages')
                ->label('Или изображение');
        }

        if ($withLink) {
            $schema[] = TextInput::make('link_url')
                ->label('Ссылка')
                ->maxLength(500);
        }

        foreach ($languages as $language) {
            $code = $language->code;
            $schema[] = TextInput::make("title_{$code}")
                ->label($type === LandingPageBlockType::Faq
                    ? "Вопрос ({$language->name})"
                    : "Заголовок ({$language->name})")
                ->columnSpanFull();

            if ($withSubtitle) {
                $schema[] = Textarea::make("subtitle_{$code}")
                    ->label("Подзаголовок ({$language->name})")
                    ->rows(2)
                    ->columnSpanFull();
            }

            if ($withRichAnswer) {
                $schema[] = RichEditor::make("content_{$code}")
                    ->label("Ответ ({$language->name})")
                    ->columnSpanFull();
            }

            if ($withImage && $type === LandingPageBlockType::Gallery) {
                $schema[] = Textarea::make("caption_{$code}")
                    ->label("Подпись ({$language->name})")
                    ->rows(2)
                    ->columnSpanFull();
            }
        }

        return Repeater::make('settings.items')
            ->label('Элементы')
            ->schema($schema)
            ->defaultItems(0)
            ->addActionLabel('Добавить элемент')
            ->reorderable()
            ->collapsible()
            ->columnSpanFull();
    }

    protected static function typeFromGet(Get $get): ?LandingPageBlockType
    {
        $value = $get('type');

        if ($value instanceof LandingPageBlockType) {
            return $value;
        }

        return is_string($value) ? LandingPageBlockType::tryFrom($value) : null;
    }
}
