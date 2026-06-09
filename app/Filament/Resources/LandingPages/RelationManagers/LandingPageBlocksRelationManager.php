<?php

namespace App\Filament\Resources\LandingPages\RelationManagers;

use App\Enums\LandingPageBlockType;
use App\Filament\Resources\LandingPages\Schemas\LandingPageBlockForm;
use App\Filament\Resources\Products\RelationManagers\Concerns\ManagesTranslationsOnActions;
use App\Filament\Support\TranslationFormHelper;
use App\Models\LandingPageBlock;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LandingPageBlocksRelationManager extends RelationManager
{
    use ManagesTranslationsOnActions {
        makeTranslationEditAction as protected traitMakeTranslationEditAction;
    }

    protected static string $relationship = 'blocks';

    protected static ?string $title = 'Блоки страницы';

    protected static function translationConfigKey(): string
    {
        return 'landing_page_block';
    }

    protected function translationFields(): ?array
    {
        return LandingPageBlockForm::translationFields();
    }

    public function form(Schema $schema): Schema
    {
        return LandingPageBlockForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        $defaultLocale = (string) config('shop.default_language', 'pl');

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('translates'))
            ->paginated(false)
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof LandingPageBlockType
                        ? $state->label()
                        : LandingPageBlockType::tryFrom((string) $state)?->label() ?? $state),
                TextColumn::make('preview')
                    ->label('Заголовок')
                    ->getStateUsing(fn (LandingPageBlock $record): string => $record->translate('title', $defaultLocale)
                        ?? $record->translate('subtitle', $defaultLocale)
                        ?? '—'),
                IconColumn::make('is_active')
                    ->label('Вкл')
                    ->boolean(),
            ])
            ->headerActions([
                $this->makeTranslationCreateAction(),
            ])
            ->recordActions([
                $this->makeTranslationEditAction(),
                DeleteAction::make(),
            ]);
    }

    protected function makeTranslationEditAction(): EditAction
    {
        return $this->traitMakeTranslationEditAction()
            ->mutateRecordDataUsing(function (array $data, LandingPageBlock $record): array {
                $data = array_merge(
                    $data,
                    TranslationFormHelper::defaults($record, static::translationConfigKey(), LandingPageBlockForm::translationFields()),
                );

                if (! is_array($data['settings'] ?? null)) {
                    $data['settings'] = $record->settings ?? [];
                }

                return $data;
            });
    }
}
