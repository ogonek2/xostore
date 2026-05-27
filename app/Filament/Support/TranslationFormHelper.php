<?php

namespace App\Filament\Support;

use App\Models\Language;
use App\Services\Translation\AutoTranslator;
use App\Support\Translation\RichTextTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class TranslationFormHelper
{
    /** @return list<string> */
    public static function fields(string $configKey): array
    {
        return config("shop.{$configKey}.translatable_fields", ['name', 'slug']);
    }

    /** @return array<string, mixed> */
    public static function defaults(?Model $record, string $configKey, ?array $onlyFields = null): array
    {
        $data = [];
        $fields = $onlyFields ?? static::fields($configKey);
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, array<string, mixed>>
     */
    public static function extract(array &$data, string $configKey, ?array $onlyFields = null): array
    {
        $translationData = [];
        $fields = $onlyFields ?? static::fields($configKey);
        $languages = Language::query()->where('is_active', true)->pluck('code');

        foreach ($languages as $code) {
            foreach ($fields as $field) {
                $key = "trans_{$code}_{$field}";
                if (array_key_exists($key, $data)) {
                    $translationData[$code][$field] = $data[$key];
                    unset($data[$key]);
                }
            }
        }

        return $translationData;
    }

    /**
     * @param  array<string, array<string, mixed>>  $translationData
     * @return array{auto_translated: int, auto_translate_failed: bool}
     */
    public static function save(Model $record, array $translationData, string $configKey, ?array $onlyFields = null): array
    {
        $fields = $onlyFields ?? static::fields($configKey);
        $sourceCode = (string) config('shop.default_language', 'pl');
        $translationData = static::normalizeSlugs($translationData, $fields);

        $record->loadMissing('translates');
        $changedSourceFields = static::detectChangedSourceFields($record, $translationData, $fields, $sourceCode);

        foreach ($translationData as $code => $fieldValues) {
            foreach ($fields as $field) {
                $value = static::normalizeValue($fieldValues[$field] ?? null);

                if ($value === null || $value === '') {
                    continue;
                }

                if ($field === 'slug') {
                    $value = Str::slug($value);
                }

                if (
                    $code !== $sourceCode
                    && static::localeUsesAutoTranslate($code)
                    && ! static::submittedValueChanged($record, $translationData, $code, $field)
                ) {
                    continue;
                }

                $record->setTranslation($field, $value, $code);
            }
        }

        return static::autoTranslateMissing($record, $translationData, $configKey, $fields, $changedSourceFields);
    }

    protected static function normalizeValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return trim((string) $value) === '' ? null : (string) $value;
    }

    protected static function hasTranslatableSource(array $fields): bool
    {
        foreach ($fields as $value) {
            $normalized = static::normalizeValue($value);

            if ($normalized !== null && $normalized !== '') {
                return true;
            }
        }

        return false;
    }

    protected static function isEmptyTranslation(mixed $value): bool
    {
        $normalized = static::normalizeValue($value);

        if ($normalized === null || $normalized === '') {
            return true;
        }

        if (str_contains($normalized, '"type":"doc"') || str_starts_with($normalized, '<')) {
            return RichTextTranslation::extractPlainText($normalized) === '';
        }

        return false;
    }

    /**
     * @param  array<string, array<string, mixed>>  $translationData
     * @param  list<string>  $fields
     * @return array<string, array<string, mixed>>
     */
    protected static function normalizeSlugs(array $translationData, array $fields): array
    {
        if (! in_array('slug', $fields, true)) {
            return $translationData;
        }

        foreach ($translationData as $locale => $fieldValues) {
            $slug = static::normalizeValue($fieldValues['slug'] ?? null);
            $source = static::normalizeValue(
                $fieldValues['name'] ?? $fieldValues['title'] ?? $fieldValues['label'] ?? null,
            );

            if ($slug !== null && $slug !== '') {
                $translationData[$locale]['slug'] = ($source !== null && $slug === $source)
                    ? Str::slug($source)
                    : Str::slug($slug);

                continue;
            }

            if ($source === null || $source === '') {
                continue;
            }

            $translationData[$locale]['slug'] = Str::slug($source);
        }

        return $translationData;
    }

    /**
     * @param  array<string, array<string, mixed>>  $translationData
     * @param  list<string>  $fields
     * @return array{auto_translated: int, auto_translate_failed: bool}
     */
    /**
     * @param  list<string>  $fields
     * @param  list<string>  $changedSourceFields
     */
    protected static function autoTranslateMissing(
        Model $record,
        array $translationData,
        string $configKey,
        array $fields,
        array $changedSourceFields,
    ): array {
        $sourceCode = (string) config('shop.default_language', 'pl');
        $sourceFields = $translationData[$sourceCode] ?? [];

        if (! static::hasTranslatableSource($sourceFields)) {
            return ['auto_translated' => 0, 'auto_translate_failed' => false];
        }

        $record->loadMissing('translates');
        $translator = app(AutoTranslator::class);
        $orderedFields = static::orderedTranslatableFields($fields);
        $contextName = static::normalizeValue(
            data_get($sourceFields, 'name') ?? $record->translate('name', $sourceCode),
        );

        $targetLanguages = Language::query()
            ->where('is_active', true)
            ->where('auto_translate_on_create', true)
            ->where('code', '!=', $sourceCode)
            ->orderBy('sort_order')
            ->get();

        $autoTranslated = 0;
        $autoTranslateFailed = false;

        foreach ($targetLanguages as $language) {
            $locale = $language->code;
            $targetName = static::resolveTargetName(
                $record,
                $translationData,
                $sourceFields,
                $sourceCode,
                $locale,
                $translator,
                $contextName,
                $autoTranslated,
                $autoTranslateFailed,
                $changedSourceFields,
            );

            if (in_array('slug', $fields, true) && static::shouldRegenerateSlug($record, $translationData, $locale, $targetName, $changedSourceFields)) {
                if ($targetName !== null && $targetName !== '') {
                    $record->setTranslation('slug', Str::slug($targetName), $locale, true);
                    $autoTranslated++;
                } else {
                    $autoTranslateFailed = true;
                }
            }

            foreach ($orderedFields as $field) {
                if (in_array($field, ['slug', 'name'], true)) {
                    continue;
                }

                if (! static::targetNeedsAutoTranslation($record, $translationData, $locale, $field, $changedSourceFields)) {
                    continue;
                }

                $source = static::normalizeValue(
                    data_get($sourceFields, $field) ?? $record->translate($field, $sourceCode),
                );

                if ($source === null || $source === '') {
                    continue;
                }

                if (static::isPlaceholderText($field, $source)) {
                    static::clearPlaceholderTarget($record, $locale, $field);

                    continue;
                }

                if ($field === 'description') {
                    $plain = RichTextTranslation::extractPlainText($source);

                    if ($plain === '' || static::isPlaceholderText($field, $plain)) {
                        continue;
                    }

                    $translated = $translator->translate($plain, $sourceCode, $locale, $contextName);

                    if ($translated === null) {
                        $autoTranslateFailed = true;

                        continue;
                    }

                    $record->setTranslation(
                        $field,
                        RichTextTranslation::fromPlainText($translated),
                        $locale,
                        true,
                    );
                    $autoTranslated++;

                    continue;
                }

                $translated = $translator->translate($source, $sourceCode, $locale, $contextName);

                if ($translated === null) {
                    $autoTranslateFailed = true;

                    continue;
                }

                $record->setTranslation($field, $translated, $locale, true);
                $autoTranslated++;
            }
        }

        return [
            'auto_translated' => $autoTranslated,
            'auto_translate_failed' => $autoTranslateFailed,
        ];
    }

    /**
     * @param  list<string>  $fields
     * @return list<string>
     */
    protected static function orderedTranslatableFields(array $fields): array
    {
        $priority = [
            'name',
            'subtitle',
            'short_description',
            'fit_description',
            'fabric_description',
            'tailoring_description',
            'meta_title',
            'meta_description',
            'description',
        ];

        $ordered = [];

        foreach ($priority as $field) {
            if (in_array($field, $fields, true)) {
                $ordered[] = $field;
            }
        }

        foreach ($fields as $field) {
            if ($field === 'slug' || in_array($field, $ordered, true)) {
                continue;
            }

            $ordered[] = $field;
        }

        return $ordered;
    }

    /**
     * @param  array<string, mixed>  $sourceFields
     * @param  array<string, array<string, mixed>>  $translationData
     */
    protected static function resolveTargetName(
        Model $record,
        array $translationData,
        array $sourceFields,
        string $sourceCode,
        string $locale,
        AutoTranslator $translator,
        ?string $contextName,
        int &$autoTranslated,
        bool &$autoTranslateFailed,
        array $changedSourceFields,
    ): ?string {
        if (static::submittedValueChanged($record, $translationData, $locale, 'name')) {
            return static::normalizeValue(data_get($translationData, "{$locale}.name"));
        }

        $existing = static::normalizeValue($record->translate('name', $locale));

        $sourceName = static::normalizeValue(
            data_get($sourceFields, 'name') ?? $record->translate('name', $sourceCode),
        );

        if ($sourceName === null || $sourceName === '') {
            return $existing;
        }

        if (! static::targetNeedsAutoTranslation($record, $translationData, $locale, 'name', $changedSourceFields)) {
            return $existing;
        }

        $translated = $translator->translate($sourceName, $sourceCode, $locale, $contextName);

        if ($translated === null) {
            $autoTranslateFailed = true;

            return $existing;
        }

        $record->setTranslation('name', $translated, $locale, true);
        $autoTranslated++;

        return $translated;
    }

    /**
     * @param  array<string, array<string, mixed>>  $translationData
     */
    protected static function targetNeedsAutoTranslation(
        Model $record,
        array $translationData,
        string $locale,
        string $field,
        array $changedSourceFields,
    ): bool {
        if (static::submittedValueChanged($record, $translationData, $locale, $field)) {
            return false;
        }

        if (in_array($field, $changedSourceFields, true)) {
            return true;
        }

        $languageId = Language::query()->where('code', $locale)->value('id');

        $row = $record->translates->first(
            fn ($translate) => $translate->language_id === $languageId && $translate->field === $field,
        );

        if ($row === null || static::isEmptyTranslation($row->value)) {
            return true;
        }

        if ($row->is_machine_translated) {
            return true;
        }

        return static::isPlaceholderText($field, (string) $row->value);
    }

    protected static function isMachineTranslated(Model $record, string $field, string $locale): bool
    {
        $languageId = Language::query()->where('code', $locale)->value('id');

        $row = $record->translates->first(
            fn ($translate) => $translate->language_id === $languageId && $translate->field === $field,
        );

        return (bool) ($row?->is_machine_translated);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translationData
     */
    protected static function shouldRegenerateSlug(
        Model $record,
        array $translationData,
        string $locale,
        ?string $targetName,
        array $changedSourceFields,
    ): bool {
        if (
            $targetName !== null
            && $targetName !== ''
            && in_array('name', $changedSourceFields, true)
        ) {
            return true;
        }

        $submittedSlug = data_get($translationData, "{$locale}.slug");

        if (
            array_key_exists('slug', $translationData[$locale] ?? [])
            && static::isEmptyTranslation($submittedSlug)
            && $targetName !== null
            && $targetName !== ''
        ) {
            return true;
        }

        if (static::submittedValueChanged($record, $translationData, $locale, 'slug')) {
            return false;
        }

        if (static::targetNeedsAutoTranslation($record, $translationData, $locale, 'slug', $changedSourceFields)) {
            return $targetName !== null && $targetName !== '';
        }

        if ($targetName === null || $targetName === '') {
            return false;
        }

        $existing = static::normalizeValue($record->translate('slug', $locale));

        if ($existing === null || $existing === '') {
            return true;
        }

        if (static::isMachineTranslated($record, 'slug', $locale)) {
            return true;
        }

        if (! static::isMachineTranslated($record, 'name', $locale)) {
            return false;
        }

        return Str::slug($existing) !== Str::slug($targetName);
    }

    protected static function clearPlaceholderTarget(Model $record, string $locale, string $field): void
    {
        $existing = $record->translate($field, $locale);

        if ($existing === null || $existing === '') {
            return;
        }

        if (
            ! static::isPlaceholderText($field, $existing)
            && ! static::isMachineTranslated($record, $field, $locale)
            && ! static::isEmptyTranslation($existing)
        ) {
            return;
        }

        $languageId = Language::query()->where('code', $locale)->value('id');

        $record->translates()
            ->where('language_id', $languageId)
            ->where('field', $field)
            ->delete();

        $record->load('translates');
    }

    protected static function localeUsesAutoTranslate(string $locale): bool
    {
        $sourceCode = (string) config('shop.default_language', 'pl');

        if ($locale === $sourceCode) {
            return false;
        }

        return Language::query()
            ->where('code', $locale)
            ->where('is_active', true)
            ->where('auto_translate_on_create', true)
            ->exists();
    }

    /**
     * @param  array<string, array<string, mixed>>  $translationData
     */
    protected static function submittedValueChanged(
        Model $record,
        array $translationData,
        string $locale,
        string $field,
    ): bool {
        if (! array_key_exists($field, $translationData[$locale] ?? [])) {
            return false;
        }

        $submitted = static::normalizeValue(data_get($translationData, "{$locale}.{$field}"));
        $existing = static::normalizeValue($record->translate($field, $locale));

        if ($field === 'slug') {
            $submitted = $submitted !== null ? Str::slug($submitted) : null;
            $existing = $existing !== null ? Str::slug($existing) : null;
        }

        if ($field === 'description') {
            $submittedPlain = RichTextTranslation::extractPlainText($submitted ?? '');
            $existingPlain = RichTextTranslation::extractPlainText($existing ?? '');

            if ($submittedPlain === '' && $existingPlain === '') {
                return false;
            }

            return $submittedPlain !== $existingPlain;
        }

        if (static::isEmptyTranslation($submitted)) {
            return ! static::isEmptyTranslation($existing);
        }

        return $submitted !== $existing;
    }

    /**
     * @param  array<string, array<string, mixed>>  $translationData
     */
    /**
     * @param  array<string, array<string, mixed>>  $translationData
     * @param  list<string>  $fields
     * @return list<string>
     */
    protected static function detectChangedSourceFields(
        Model $record,
        array $translationData,
        array $fields,
        string $sourceCode,
    ): array {
        $changed = [];

        foreach ($fields as $field) {
            if (static::submittedValueChanged($record, $translationData, $sourceCode, $field)) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    protected static function isPlaceholderText(string $field, string $text): bool
    {
        $normalized = mb_strtolower(trim($text));

        if ($normalized === '') {
            return true;
        }

        $placeholders = [
            'subtitle' => [
                'krótki opis',
                'krotki opis',
                'short description',
                'brief description',
                'podtytuł',
                'podtytul',
                'subtitle',
                'подзаголовок',
            ],
            'short_description' => [
                'krótki opis',
                'krotki opis',
                'short description',
                'brief description',
                'краткое описание',
                'opis skrócony',
                'opis skrocony',
            ],
            'description' => [
                'opis',
                'description',
                'описание',
                'enter description',
                'wprowadź opis',
            ],
            'fit_description' => [
                'opis dopasowania',
                'fit description',
            ],
            'fabric_description' => [
                'opis tkaniny',
                'fabric description',
            ],
            'tailoring_description' => [
                'opis krawiectwa',
                'tailoring description',
            ],
        ];

        return in_array($normalized, $placeholders[$field] ?? [], true);
    }
}
