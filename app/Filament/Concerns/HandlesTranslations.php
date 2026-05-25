<?php

namespace App\Filament\Concerns;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;

trait HandlesTranslations
{
    protected array $pendingTranslations = [];

    protected function getTranslationConfigKey(): string
    {
        return 'category';
    }

    protected function getTranslationFields(): array
    {
        return config('shop.'.$this->getTranslationConfigKey().'.translatable_fields', ['name', 'slug']);
    }

    protected function translationFormDefaults(?Model $record = null): array
    {
        $data = [];
        $fields = $this->getTranslationFields();
        $languages = Language::query()->where('is_active', true)->orderBy('sort_order')->get();

        foreach ($languages as $language) {
            foreach ($fields as $field) {
                $key = "trans_{$language->code}_{$field}";
                $data[$key] = $record
                    ? $record->translate($field, $language->code)
                    : null;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->getRecord()->loadMissing('translates');

        return array_merge($data, $this->translationFormDefaults($this->getRecord()));
    }

    protected function extractTranslationData(array &$data): array
    {
        $translationData = [];
        $languages = Language::query()->where('is_active', true)->pluck('code');

        foreach ($languages as $code) {
            foreach ($this->getTranslationFields() as $field) {
                $key = "trans_{$code}_{$field}";
                if (array_key_exists($key, $data)) {
                    $translationData[$code][$field] = $data[$key];
                    unset($data[$key]);
                }
            }
        }

        return $translationData;
    }

    protected function saveTranslations(Model $record, array $translationData): void
    {
        foreach ($translationData as $code => $fields) {
            foreach ($fields as $field => $value) {
                if ($value !== null && $value !== '') {
                    $record->setTranslation($field, $value, $code);
                }
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingTranslations = $this->extractTranslationData($data);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingTranslations = $this->extractTranslationData($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! empty($this->pendingTranslations)) {
            $this->saveTranslations($this->getRecord(), $this->pendingTranslations);
        }

        parent::afterCreate();
    }

    protected function afterSave(): void
    {
        if (! empty($this->pendingTranslations)) {
            $this->saveTranslations($this->getRecord(), $this->pendingTranslations);
        }

        parent::afterSave();
    }
}
