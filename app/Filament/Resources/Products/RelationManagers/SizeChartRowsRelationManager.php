<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\ProductSizeGridOptions;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizeChartRowsRelationManager extends RelationManager
{
    protected static string $relationship = 'sizeChartRows';

    protected static ?string $title = 'Таблица мерок';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('size')
                ->label('Размер')
                ->maxLength(32),
            TextInput::make('chest')
                ->label('Грудь')
                ->maxLength(32),
            TextInput::make('waist')
                ->label('Талия')
                ->maxLength(32),
            TextInput::make('hips')
                ->label('Бёдра')
                ->maxLength(32),
            TextInput::make('inseam')
                ->label('Шов')
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
                Action::make('fillFromPreset')
                    ->label('Заполнить размеры из пресета')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->modalDescription('Создаёт строки с колонкой «Размер» из пресета товара. Мерки (грудь, талия…) заполните вручную.')
                    ->visible(fn (): bool => filled($this->getOwnerRecord()->size_grid_id))
                    ->action(function (): void {
                        /** @var Product $product */
                        $product = $this->getOwnerRecord();
                        $seeds = ProductSizeGridOptions::emptyChartRows($product->size_grid_id);

                        if ($seeds === []) {
                            Notification::make()
                                ->title('Пресет пуст')
                                ->body('Назначьте пресет на вкладке «Пресет размеров» или добавьте размеры в справочнике.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $existing = $product->sizeChartRows()->pluck('size')->filter()->all();
                        $created = 0;

                        foreach ($seeds as $row) {
                            if (in_array($row['size'], $existing, true)) {
                                continue;
                            }

                            $product->sizeChartRows()->create($row);
                            $created++;
                        }

                        Notification::make()
                            ->title($created > 0 ? 'Строки добавлены' : 'Без изменений')
                            ->body($created > 0
                                ? "Добавлено строк: {$created}. Заполните мерки в таблице."
                                : 'Все размеры из пресета уже есть в таблице.')
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
            ->emptyStateHeading('Нет строк размерной сетки')
            ->emptyStateDescription('Ручные мерки для таблицы на сайте. Размеры S/M/L для вариантов — пресет на вкладке «Пресет размеров» в «Основное».');
    }
}
