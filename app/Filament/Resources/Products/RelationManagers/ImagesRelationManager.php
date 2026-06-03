<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\FilamentMedia;
use App\Models\Product;
use App\Support\Media\Media;
use App\Support\Shop\ProductGalleryBulkUpload;
use App\Support\Shop\ProductImageAltGenerator;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Галерея';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FilamentMedia::image('path', 'products')
                ->label('Фото')
                ->required()
                ->columnSpanFull(),
            TextInput::make('alt')
                ->label('Alt')
                ->maxLength(255),
            TextInput::make('sort_order')
                ->label('Сорт.')
                ->numeric()
                ->default(0),
            Toggle::make('is_primary')
                ->label('Главное'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Фото')
                    ->disk(fn ($record) => $record->disk ?? Media::disk()),
                TextColumn::make('alt')
                    ->label('Alt')
                    ->placeholder('—')
                    ->wrap(),
                IconColumn::make('is_primary')
                    ->label('Главное')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Сорт.')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                Action::make('bulkUpload')
                    ->label('Массовая загрузка')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->modalHeading('Массовая загрузка в галерею')
                    ->modalDescription('Загрузите несколько фото за раз. Подписи Alt создадутся автоматически по названию товара.')
                    ->modalSubmitActionLabel('Добавить в галерею')
                    ->form([
                        FilamentMedia::gallery('paths', 'products'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Product $product */
                        $product = $this->getOwnerRecord();

                        $created = app(ProductGalleryBulkUpload::class)->store(
                            $product,
                            $data['paths'] ?? [],
                        );

                        if ($created === 0) {
                            Notification::make()
                                ->title('Фото не добавлены')
                                ->body('Выберите хотя бы один файл.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Галерея обновлена')
                            ->body("Добавлено фотографий: {$created}. Alt заполнен автоматически.")
                            ->success()
                            ->send();
                    }),
                CreateAction::make()
                    ->label('Добавить фото')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (filled($data['alt'] ?? null)) {
                            return $data;
                        }

                        /** @var Product $product */
                        $product = $this->getOwnerRecord();
                        $sequence = (int) $product->images()->count() + 1;
                        $data['alt'] = ProductImageAltGenerator::generate($product, $sequence);

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет фотографий')
            ->emptyStateDescription('Добавьте фото по одному или используйте массовую загрузку.');
    }
}
