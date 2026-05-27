<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Forms\TranslationTabs;
use App\Filament\Resources\Products\RelationManagers\Concerns\ManagesTranslationsOnActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductDetailsRelationManager extends RelationManager
{
    use ManagesTranslationsOnActions;

    protected static string $relationship = 'detailItems';

    protected static ?string $title = 'Детали (как у FIGS)';

    protected static function translationConfigKey(): string
    {
        return 'product_detail_item';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TranslationTabs::make(static::translationConfigKey(), 'Переводы', [
                'label',
                'description',
            ]),
            TextInput::make('sort_order')
                ->label('Сорт.')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('translates'))
            ->columns([
                TextColumn::make('label_pl')
                    ->label('Пункт')
                    ->state(fn ($record) => $record->translate('label', 'pl') ?? '—'),
                TextColumn::make('description_pl')
                    ->label('Описание')
                    ->limit(60)
                    ->state(fn ($record) => $record->translate('description', 'pl') ?? '—'),
                TextColumn::make('sort_order')
                    ->label('Сорт.')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                $this->makeTranslationCreateAction()
                    ->label('Новый пункт'),
            ])
            ->recordActions([
                $this->makeTranslationEditAction(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет пунктов деталей')
            ->emptyStateDescription('Добавьте характеристики товара в стиле FIGS.');
    }
}
