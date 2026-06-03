<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\ProductAdminOptions;
use App\Filament\Support\ProductSizeGridOptions;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Shop\ProductVariantColorSync;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Размеры';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sku')
                ->label('SKU')
                ->required()
                ->maxLength(64),
            Placeholder::make('product_color')
                ->label('Цвет товара')
                ->content(function (): string {
                    $product = $this->getOwnerRecord();
                    $parts = array_filter([
                        $product->color_label,
                        $product->color_hex,
                    ]);

                    return $parts === []
                        ? 'Задайте цвет на вкладке «Основное» (поля «Цвет» и HEX).'
                        : implode(' · ', $parts);
                })
                ->columnSpanFull(),
            Select::make('size_grid_value_id')
                ->label('Размер')
                ->options(fn () => ProductAdminOptions::sizeGridValues(
                    $this->getOwnerRecord()->size_grid_id,
                ))
                ->searchable()
                ->nullable()
                ->helperText(fn () => $this->getOwnerRecord()->size_grid_id
                    ? null
                    : 'Сначала выберите пресет: кнопка «Пресет размерной сетки» выше или вкладка «Основное» → «Пресет размеров».'),
            TextInput::make('price')
                ->label('Цена')
                ->numeric()
                ->required()
                ->prefix('PLN'),
            TextInput::make('compare_at_price')
                ->label('Цена до скидки')
                ->numeric()
                ->prefix('PLN'),
            TextInput::make('barcode')
                ->label('Штрихкод')
                ->maxLength(64),
            Toggle::make('is_default')
                ->label('По умолчанию'),
            Toggle::make('is_active')
                ->label('Активен')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['sizeGridValue', 'attributeValues']))
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('sizeGridValue.display_value')
                    ->label('Размер')
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('Цена')
                    ->money('PLN'),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Action::make('assignSizeGridPreset')
                    ->label('Пресет размерной сетки')
                    ->icon('heroicon-o-table-cells')
                    ->modalHeading('Выбор пресета из справочника')
                    ->modalDescription('Пресеты настраиваются в «Каталог → Размерные сетки». После выбора сохраните товар, если меняли пресет на вкладке «Основное».')
                    ->fillForm(fn (): array => [
                        'size_grid_id' => $this->getOwnerRecord()->size_grid_id,
                    ])
                    ->form([
                        Select::make('size_grid_id')
                            ->label('Пресет')
                            ->options(fn () => ProductSizeGridOptions::presets(
                                $this->getOwnerRecord()->primary_category_id,
                            ))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->nullable()
                            ->helperText('Пусто — без пресета. Показаны сетки категории и универсальные (без привязки к категории).'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Product $product */
                        $product = $this->getOwnerRecord();
                        $product->update(['size_grid_id' => $data['size_grid_id'] ?? null]);

                        $labels = ProductSizeGridOptions::sizeLabels($product->size_grid_id);

                        Notification::make()
                            ->title($product->size_grid_id ? 'Пресет назначен' : 'Пресет снят')
                            ->body($labels === []
                                ? 'Добавьте размеры в справочнике пресета, если список пуст.'
                                : 'Размеры: '.implode(', ', $labels))
                            ->success()
                            ->send();
                    }),
                CreateAction::make()
                    ->label('Новый вариант')
                    ->after(function (ProductVariant $record): void {
                        ProductVariantColorSync::syncVariant($this->getOwnerRecord(), $record);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (ProductVariant $record): void {
                        ProductVariantColorSync::syncVariant($this->getOwnerRecord(), $record);
                    }),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет вариантов')
            ->emptyStateDescription('Выберите пресет кнопкой выше, затем создайте варианты с размером и ценой.');
    }

}
