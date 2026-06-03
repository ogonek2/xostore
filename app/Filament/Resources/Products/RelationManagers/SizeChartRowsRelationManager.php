<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\ProductSizeChartPresetOptions;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizeChartRowsRelationManager extends RelationManager
{
    protected static string $relationship = 'sizeChartRows';

    protected static ?string $title = 'Таблица мерок товара';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('size')
                ->label('Размер')
                ->maxLength(32),
            TextInput::make('chest')
                ->label('Грудь')
                ->maxLength(32)
                ->placeholder('86 cm'),
            TextInput::make('waist')
                ->label('Талия')
                ->maxLength(32),
            TextInput::make('hips')
                ->label('Бёдра')
                ->maxLength(32),
            TextInput::make('inseam')
                ->label('Внутр. шов')
                ->maxLength(32),
            TextInput::make('sort_order')
                ->label('Сорт.')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('size')->label('Размер'),
                TextColumn::make('chest')->label('Грудь'),
                TextColumn::make('waist')->label('Талия'),
                TextColumn::make('hips')->label('Бёдра'),
                TextColumn::make('inseam')->label('Шов'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                Action::make('applyChartPreset')
                    ->label('Применить пресет таблицы мерок')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->modalHeading('Пресет таблицы мерок (см)')
                    ->modalDescription('Копирует все строки с точными значениями в сантиметрах. Пресеты: Каталог → Таблицы мерок (см).')
                    ->fillForm(fn (): array => [
                        'size_chart_preset_id' => $this->getOwnerRecord()->size_chart_preset_id
                            ? (string) $this->getOwnerRecord()->size_chart_preset_id
                            : null,
                    ])
                    ->form([
                        Select::make('size_chart_preset_id')
                            ->label('Пресет')
                            ->options(fn (): array => ProductSizeChartPresetOptions::presets(
                                $this->getOwnerRecord()->size_chart_preset_id,
                            ))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Product $product */
                        $product = $this->getOwnerRecord();
                        $presetId = (int) $data['size_chart_preset_id'];
                        $rows = ProductSizeChartPresetOptions::rowsForProductCopy($presetId);

                        if ($rows === []) {
                            Notification::make()
                                ->title('Пресет пуст')
                                ->warning()
                                ->send();

                            return;
                        }

                        $product->sizeChartRows()->delete();
                        foreach ($rows as $row) {
                            $product->sizeChartRows()->create($row);
                        }

                        $product->update(['size_chart_preset_id' => $presetId]);

                        Notification::make()
                            ->title('Таблица мерок применена')
                            ->body('Скопировано строк: '.count($rows).'. При необходимости отредактируйте значения.')
                            ->success()
                            ->send();
                    }),
                CreateAction::make()
                    ->label('Новая строка'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет своих строк')
            ->emptyStateDescription('Назначьте пресет на вкладке «Таблица мерок» в карточке товара или примените пресет кнопкой выше. Пустая таблица на сайте берётся из пресета автоматически.');
    }
}
