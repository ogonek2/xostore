<?php

namespace App\Filament\Resources\Feeds\Schemas;

use App\Models\FeedSettings;
use App\Models\Language;
use App\Support\Feeds\FeedField;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language as CodeLanguage;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class FeedSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('feeds')
                ->tabs([
                    Tab::make('general')
                        ->label('Общие')
                        ->schema([
                            Section::make('Автоматизация')
                                ->description('Фиды обновляются автоматически при сохранении, удалении товаров, вариантов и изображений.')
                                ->schema([
                                    Toggle::make('auto_regenerate')
                                        ->label('Автообновление при изменении каталога')
                                        ->default(true),
                                    Toggle::make('include_out_of_stock')
                                        ->label('Включать товары не в наличии')
                                        ->helperText('Если выключено — в фид попадают только позиции in stock.'),
                                    Select::make('locale')
                                        ->label('Язык фида')
                                        ->options(fn (): array => Language::query()
                                            ->where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->pluck('name', 'code')
                                            ->all())
                                        ->required()
                                        ->native(false),
                                    TextInput::make('product_condition')
                                        ->label('Состояние товара')
                                        ->default('new')
                                        ->required()
                                        ->maxLength(32)
                                        ->helperText('Обычно: new, refurbished, used'),
                                    TextInput::make('google_product_category')
                                        ->label('Категория Google по умолчанию')
                                        ->placeholder('Apparel & Accessories > Clothing')
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                    Select::make('id_source')
                                        ->label('Источник item_id (id в фиде)')
                                        ->options([
                                            FeedSettings::ID_SOURCE_PRODUCT => 'ID товара в каталоге (product_id)',
                                            FeedSettings::ID_SOURCE_VARIANT_SKU => 'SKU варианта',
                                            FeedSettings::ID_SOURCE_VARIANT_ID => 'ID варианта',
                                        ])
                                        ->default(FeedSettings::ID_SOURCE_PRODUCT)
                                        ->required()
                                        ->native(false)
                                        ->helperText('Для сопоставления с GA4/Meta Pixel выбирайте ID товара в каталоге. В этом режиме — одна строка на товар.')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),
                    Tab::make('fields')
                        ->label('Поля фида')
                        ->schema([
                            Section::make('Какие поля включать в выгрузку')
                                ->description('Управляет колонками/тегами самого фида (Google Merchant / Facebook Catalog), а не полями таблицы товаров. Обязательные поля (id, title, link, image_link, availability, price) всегда включены. Если выключить brand — название бренда также вырезается из title через регулярные выражения.')
                                ->schema([
                                    Placeholder::make('required_fields_hint')
                                        ->label('Обязательные поля')
                                        ->content(implode(', ', FeedField::REQUIRED))
                                        ->columnSpanFull(),
                                    CheckboxList::make('enabled_fields')
                                        ->label('Опциональные поля')
                                        ->options(FeedField::optionalLabels())
                                        ->default(FeedField::OPTIONAL)
                                        ->columns(2)
                                        ->bulkToggleable()
                                        ->helperText('Снимите галочку, чтобы исключить поле из Google XML и Facebook CSV.')
                                        ->dehydrateStateUsing(function (?array $state): array {
                                            $optional = array_values(array_intersect(
                                                FeedField::OPTIONAL,
                                                is_array($state) ? $state : [],
                                            ));

                                            return array_values(array_unique(array_merge(
                                                FeedField::REQUIRED,
                                                $optional,
                                            )));
                                        })
                                        ->afterStateHydrated(function (CheckboxList $component, mixed $state): void {
                                            $enabled = is_array($state) ? $state : FeedField::defaultEnabled();
                                            $component->state(array_values(array_intersect(
                                                FeedField::OPTIONAL,
                                                $enabled,
                                            )));
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('editor')
                        ->label('Редактор')
                        ->schema([
                            Section::make('Ручное редактирование фидов')
                                ->description('Можно править готовые файлы Google (XML) и Facebook (CSV). Включите блокировку, чтобы автообновление и кнопка «Обновить фиды» не затирали ручные правки. При сохранении настроек с включённой блокировкой содержимое редакторов записывается в файлы.')
                                ->schema([
                                    Toggle::make('manual_feeds_locked')
                                        ->label('Блокировать перезапись ручных правок')
                                        ->helperText('Пока включено — каталог не пересобирает файлы автоматически. Снимите галочку и нажмите «Обновить фиды», чтобы снова сгенерировать из каталога.')
                                        ->columnSpanFull(),
                                    CodeEditor::make('google_feed_content')
                                        ->label('Google Merchant (XML)')
                                        ->language(CodeLanguage::Xml)
                                        ->helperText('Публичный файл google-merchant (или ваш slug). Сохраняется при записи настроек, если включена блокировка ручных правок.')
                                        ->columnSpanFull(),
                                    CodeEditor::make('facebook_feed_content')
                                        ->label('Facebook Catalog (CSV)')
                                        ->helperText('UTF-8 CSV. BOM добавляется автоматически при сохранении.')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('google')
                        ->label('Google Merchant')
                        ->schema([
                            Section::make('Google Merchant Center')
                                ->schema([
                                    Toggle::make('google_enabled')
                                        ->label('Включить фид Google')
                                        ->live(),
                                    TextInput::make('google_slug')
                                        ->label('Имя файла / URL')
                                        ->required()
                                        ->maxLength(120)
                                        ->regex('/^[\w\.\-]+$/')
                                        ->helperText('Публичный URL: /feeds/{имя-файла}')
                                        ->visible(fn ($get) => $get('google_enabled')),
                                    Placeholder::make('google_public_url')
                                        ->label('Публичная ссылка')
                                        ->content(fn (?FeedSettings $record): string => $record?->googlePublicUrl() ?? '—')
                                        ->visible(fn ($get) => $get('google_enabled')),
                                ])
                                ->columns(1),
                            Section::make('Статистика Google')
                                ->schema(static::googleStatsFields())
                                ->columns(3)
                                ->visible(fn ($get) => $get('google_enabled')),
                        ]),
                    Tab::make('facebook')
                        ->label('Facebook Catalog')
                        ->schema([
                            Section::make('Meta / Facebook Catalog')
                                ->schema([
                                    Toggle::make('facebook_enabled')
                                        ->label('Включить фид Facebook')
                                        ->live(),
                                    TextInput::make('facebook_slug')
                                        ->label('Имя файла / URL')
                                        ->required()
                                        ->maxLength(120)
                                        ->regex('/^[\w\.\-]+$/')
                                        ->helperText('Публичный URL: /feeds/{имя-файла}')
                                        ->visible(fn ($get) => $get('facebook_enabled')),
                                    Placeholder::make('facebook_public_url')
                                        ->label('Публичная ссылка')
                                        ->content(fn (?FeedSettings $record): string => $record?->facebookPublicUrl() ?? '—')
                                        ->visible(fn ($get) => $get('facebook_enabled')),
                                ])
                                ->columns(1),
                            Section::make('Статистика Facebook')
                                ->schema(static::facebookStatsFields())
                                ->columns(3)
                                ->visible(fn ($get) => $get('facebook_enabled')),
                        ]),
                    Tab::make('stats')
                        ->label('Показатели')
                        ->schema([
                            Section::make('Последняя генерация')
                                ->schema([
                                    Placeholder::make('last_duration_ms')
                                        ->label('Время генерации')
                                        ->content(fn (?FeedSettings $record): string => $record?->last_duration_ms
                                            ? $record->last_duration_ms.' мс'
                                            : '—'),
                                    Placeholder::make('last_error')
                                        ->label('Последняя ошибка')
                                        ->content(fn (?FeedSettings $record): string => $record?->last_error ?: 'Нет ошибок')
                                        ->columnSpanFull(),
                                    Placeholder::make('storage_path')
                                        ->label('Папка на диске')
                                        ->content(fn (?FeedSettings $record): string => $record
                                            ? $record->storageDisk().':'.$record->storageDirectory()
                                            : '—'),
                                    Placeholder::make('currency')
                                        ->label('Валюта')
                                        ->content(fn (): string => (string) config('shop.currency', 'PLN')),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return list<Placeholder>
     */
    private static function googleStatsFields(): array
    {
        return [
            Placeholder::make('google_last_generated_at')
                ->label('Обновлён')
                ->content(fn (?FeedSettings $record): string => $record?->google_last_generated_at?->format('d.m.Y H:i:s') ?? 'Ещё не создан'),
            Placeholder::make('google_item_count')
                ->label('Позиций')
                ->content(fn (?FeedSettings $record): string => (string) ($record?->google_item_count ?? 0)),
            Placeholder::make('google_file_size')
                ->label('Размер файла')
                ->content(fn (?FeedSettings $record): string => $record
                    ? $record->formattedFileSize((int) $record->google_file_size)
                    : '—'),
        ];
    }

    /**
     * @return list<Placeholder>
     */
    private static function facebookStatsFields(): array
    {
        return [
            Placeholder::make('facebook_last_generated_at')
                ->label('Обновлён')
                ->content(fn (?FeedSettings $record): string => $record?->facebook_last_generated_at?->format('d.m.Y H:i:s') ?? 'Ещё не создан'),
            Placeholder::make('facebook_item_count')
                ->label('Позиций')
                ->content(fn (?FeedSettings $record): string => (string) ($record?->facebook_item_count ?? 0)),
            Placeholder::make('facebook_file_size')
                ->label('Размер файла')
                ->content(fn (?FeedSettings $record): string => $record
                    ? $record->formattedFileSize((int) $record->facebook_file_size)
                    : '—'),
        ];
    }
}
