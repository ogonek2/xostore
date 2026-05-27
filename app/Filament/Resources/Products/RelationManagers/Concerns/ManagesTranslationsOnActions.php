<?php

namespace App\Filament\Resources\Products\RelationManagers\Concerns;

use App\Filament\Support\TranslationFormHelper;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;

trait ManagesTranslationsOnActions
{
    /** @var array<string, array<string, mixed>> */
    protected array $pendingRelationTranslations = [];

    abstract protected static function translationConfigKey(): string;

    protected function makeTranslationCreateAction(): CreateAction
    {
        return CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {
                $this->pendingRelationTranslations = TranslationFormHelper::extract(
                    $data,
                    static::translationConfigKey(),
                );

                return $data;
            })
            ->after(function (Model $record): void {
                if ($this->pendingRelationTranslations !== []) {
                    TranslationFormHelper::save(
                        $record,
                        $this->pendingRelationTranslations,
                        static::translationConfigKey(),
                    );
                }
            });
    }

    protected function makeTranslationEditAction(): EditAction
    {
        return EditAction::make()
            ->mutateRecordDataUsing(
                fn (array $data, Model $record): array => array_merge(
                    $data,
                    TranslationFormHelper::defaults($record, static::translationConfigKey()),
                )
            )
            ->mutateFormDataUsing(function (array $data): array {
                $this->pendingRelationTranslations = TranslationFormHelper::extract(
                    $data,
                    static::translationConfigKey(),
                );

                return $data;
            })
            ->after(function (Model $record): void {
                if ($this->pendingRelationTranslations !== []) {
                    TranslationFormHelper::save(
                        $record,
                        $this->pendingRelationTranslations,
                        static::translationConfigKey(),
                    );
                }
            });
    }
}
