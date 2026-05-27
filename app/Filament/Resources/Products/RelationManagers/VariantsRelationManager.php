<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\ProductAdminOptions;
use App\Models\ProductVariant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

    /** @var array<int, array{color_attribute_value_id?: int|null}> */
    protected array $pendingVariantColors = [];

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sku')
                ->label('SKU')
                ->required()
                ->maxLength(64),
            Select::make('color_attribute_value_id')
                ->label('Цвет')
                ->options(fn () => ProductAdminOptions::colorAttributeValues())
                ->searchable()
                ->nullable()
                ->dehydrated(false),
            Select::make('size_grid_value_id')
                ->label('Размер')
                ->options(fn () => ProductAdminOptions::sizeGridValues(
                    $this->getOwnerRecord()->size_grid_id
                ))
                ->searchable()
                ->nullable(),
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
                CreateAction::make()
                    ->label('Новый вариант')
                    ->mutateFormDataUsing(function (array $data): array {
                        $this->extractVariantColor($data);

                        return $data;
                    })
                    ->after(fn (ProductVariant $record) => $this->syncVariantColor($record)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, ProductVariant $record): array {
                        $color = $record->attributeValues->first(fn ($v) => $v->color_hex);

                        $data['color_attribute_value_id'] = $color?->id;

                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $this->extractVariantColor($data);

                        return $data;
                    })
                    ->after(fn (ProductVariant $record) => $this->syncVariantColor($record)),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет вариантов')
            ->emptyStateDescription('Создайте вариант с размером и ценой — без вариантов нельзя выбрать размер на сайте.');
    }

    protected function extractVariantColor(array &$data): void
    {
        $this->pendingVariantColors = [];

        if (isset($data['color_attribute_value_id'])) {
            $this->pendingVariantColors[0] = [
                'color_attribute_value_id' => $data['color_attribute_value_id'],
            ];
            unset($data['color_attribute_value_id']);
        }
    }

    protected function syncVariantColor(ProductVariant $variant): void
    {
        $colorId = $this->pendingVariantColors[0]['color_attribute_value_id'] ?? null;

        if ($colorId) {
            $variant->attributeValues()->sync([$colorId]);
        }
    }
}
