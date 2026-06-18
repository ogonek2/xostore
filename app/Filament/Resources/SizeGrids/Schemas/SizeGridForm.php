<?php

namespace App\Filament\Resources\SizeGrids\Schemas;

use App\Enums\SizeGridPresetKind;
use App\Filament\Forms\TranslationTabs;
use App\Support\Shop\SizeGridTemplates;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SizeGridForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Тип пресета')
                ->description('Пресет — это кнопки размера на карточке товара (S, M, L, 38…). Таблица мерок в сантиметрах настраивается отдельно: «Таблицы мерок (см)».')
                ->schema([
                    Select::make('preset_kind')
                        ->label('Для чего этот пресет')
                        ->options(SizeGridPresetKind::options())
                        ->default(SizeGridPresetKind::Custom->value)
                        ->native(false)
                        ->live()
                        ->helperText(fn (Get $get): string => SizeGridPresetKind::tryFrom((string) $get('preset_kind'))?->hint()
                            ?? 'Выберите тип — ниже можно быстро подставить типовой набор размеров.'),
                    TextInput::make('code')
                        ->label('Код (латиница)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->maxLength(64)
                        ->helperText('Уникальный код для импорта и привязки к товару. Примеры: clothing_letter_women, bags_sml, footwear_eu'),
                    TextInput::make('unit')
                        ->label('Подпись единицы')
                        ->placeholder('EU, см')
                        ->maxLength(16)
                        ->helperText('Необязательно. Показывается рядом с размерами (например EU для обуви).'),
                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Размеры для кнопок на сайте')
                ->description('«Код размера» — внутреннее значение (s, m, 25). «На сайте» — текст на кнопке (S, M, 25 см). После сохранения привяжите пресет к товару и создайте варианты на вкладке «Размеры».')
                ->schema([
                    Select::make('_quick_fill')
                        ->label('Быстро заполнить список')
                        ->options(function (Get $get): array {
                            $kind = SizeGridPresetKind::tryFrom((string) $get('preset_kind'));

                            $options = ['' => '— выберите шаблон —'];

                            if ($kind === SizeGridPresetKind::Bags) {
                                return $options + SizeGridTemplates::bagSizeAlternatives();
                            }

                            if ($kind && $kind !== SizeGridPresetKind::Custom) {
                                $options[$kind->value] = 'Стандартный набор: '.$kind->label();
                            }

                            return $options;
                        })
                        ->dehydrated(false)
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            if (blank($state)) {
                                return;
                            }

                            $kind = SizeGridPresetKind::tryFrom((string) $get('preset_kind'));
                            $values = $kind === SizeGridPresetKind::Bags
                                ? SizeGridTemplates::bagAlternativeValues($state)
                                : SizeGridTemplates::valuesFor($kind ?? $state);

                            if ($values !== []) {
                                $set('values', $values);
                            }

                            $set('_quick_fill', null);
                        })
                        ->columnSpanFull(),
                    Placeholder::make('size_grid_examples')
                        ->hiddenLabel()
                        ->content(fn (Get $get): string => match (SizeGridPresetKind::tryFrom((string) $get('preset_kind'))) {
                            SizeGridPresetKind::Bags => 'Сумки: часто S/M/L (малый/средний/большой) или размеры в см (25, 30, 35). Для клатча без размера — тип «Без размера».',
                            SizeGridPresetKind::ClothingLetters => 'Одежда: буквенные размеры XXS–XXL. На вкладке товара «Размеры» создайте вариант для каждой кнопки с ценой.',
                            SizeGridPresetKind::Footwear => 'Обувь: EU 35–42. При необходимости отредактируйте список вручную.',
                            SizeGridPresetKind::OneSize => 'Товар без выбора размера — одна кнопка One size.',
                            default => 'Выберите тип пресета или заполните размеры вручную.',
                        })
                        ->columnSpanFull(),
                    Repeater::make('values')
                        ->label('')
                        ->relationship()
                        ->schema([
                            TextInput::make('value')
                                ->label('Код размера')
                                ->required()
                                ->maxLength(32)
                                ->placeholder('s, m, 25'),
                            TextInput::make('display_value')
                                ->label('На сайте')
                                ->maxLength(32)
                                ->placeholder('S, M, 25'),
                            TextInput::make('sort_order')
                                ->label('Порядок')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Добавить размер')
                        ->collapsible()
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            TranslationTabs::make('size_grid', 'Название и описание'),
        ]);
    }
}
