<?php

namespace App\Filament\Resources\Products\RelationManagers\Concerns;

use App\Filament\Support\TranslationFormHelper;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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
                if ($this->pendingRelationTranslations === []) {
                    return;
                }

                $result = TranslationFormHelper::save(
                    $record,
                    $this->pendingRelationTranslations,
                    static::translationConfigKey(),
                );

                $this->pendingRelationTranslations = [];
                $this->notifyRelationAutoTranslation($result);
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
                if ($this->pendingRelationTranslations === []) {
                    return;
                }

                $result = TranslationFormHelper::save(
                    $record,
                    $this->pendingRelationTranslations,
                    static::translationConfigKey(),
                );

                $this->pendingRelationTranslations = [];
                $this->notifyRelationAutoTranslation($result);
            });
    }

    /**
     * @param  array{auto_translated: int, auto_translate_failed: bool}  $result
     */
    protected function notifyRelationAutoTranslation(array $result): void
    {
        if (($result['auto_translated'] ?? 0) > 0) {
            Notification::make()
                ->title('Автоперевод выполнен')
                ->body("Заполнено полей на других языках: {$result['auto_translated']}.")
                ->success()
                ->send();

            return;
        }

        if ($result['auto_translate_failed'] ?? false) {
            Notification::make()
                ->title('Автоперевод недоступен')
                ->body('Проверьте настройки API перевода или заполните переводы вручную.')
                ->warning()
                ->send();
        }
    }
}
