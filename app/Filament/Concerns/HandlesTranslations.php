<?php

namespace App\Filament\Concerns;

use App\Filament\Support\TranslationFormHelper;
use Illuminate\Database\Eloquent\Model;

trait HandlesTranslations
{
    protected array $pendingTranslations = [];

    protected int $lastAutoTranslatedCount = 0;

    protected bool $lastAutoTranslateFailed = false;

    protected function getTranslationConfigKey(): string
    {
        return 'category';
    }

    protected function getTranslationFields(): array
    {
        return TranslationFormHelper::fields($this->getTranslationConfigKey());
    }

    protected function translationFormDefaults(?Model $record = null): array
    {
        return TranslationFormHelper::defaults($record, $this->getTranslationConfigKey());
    }

    protected function fillTranslationFormData(array $data, ?Model $record = null): array
    {
        $record ??= $this->getRecord();
        $record->loadMissing('translates');

        return array_merge($data, $this->translationFormDefaults($record));
    }

    protected function extractTranslationData(array &$data): array
    {
        return TranslationFormHelper::extract($data, $this->getTranslationConfigKey());
    }

    protected function captureTranslationFormData(array &$data): array
    {
        $this->pendingTranslations = $this->extractTranslationData($data);

        return $data;
    }

    protected function saveTranslations(Model $record, array $translationData): void
    {
        $result = TranslationFormHelper::save($record, $translationData, $this->getTranslationConfigKey());

        $this->lastAutoTranslatedCount = $result['auto_translated'];
        $this->lastAutoTranslateFailed = $result['auto_translate_failed'];
    }

    protected function persistPendingTranslations(?Model $record = null): void
    {
        if (empty($this->pendingTranslations)) {
            return;
        }

        $record ??= $this->getRecord();
        $this->saveTranslations($record, $this->pendingTranslations);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->fillTranslationFormData($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->captureTranslationFormData($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->captureTranslationFormData($data);
    }

    protected function afterCreate(): void
    {
        $this->persistPendingTranslations();
    }

    protected function afterSave(): void
    {
        $this->persistPendingTranslations();
    }
}
