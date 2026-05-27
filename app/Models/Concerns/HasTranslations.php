<?php

namespace App\Models\Concerns;

use App\Models\Language;
use App\Models\Translate;
use App\Services\Locale\CurrentLanguage;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    public function translates(): MorphMany
    {
        return $this->morphMany(Translate::class, 'translatable');
    }

    public function translatableFields(): array
    {
        $key = strtolower(class_basename($this));

        return config("shop.{$key}.translatable_fields", ['name', 'slug']);
    }

    public function translate(string $field, ?string $languageCode = null, ?string $fallback = null): ?string
    {
        $language = $this->resolveLanguage($languageCode);

        $value = $this->translates
            ->first(fn (Translate $t) => $t->language_id === $language->id && $t->field === $field)
            ?->value;

        if ($value !== null && $value !== '') {
            return $value;
        }

        if ($language->code !== config('shop.fallback_language')) {
            $fallbackLanguage = Language::query()
                ->where('code', config('shop.fallback_language'))
                ->first();

            if ($fallbackLanguage) {
                $fallbackValue = $this->translates
                    ->first(fn (Translate $t) => $t->language_id === $fallbackLanguage->id && $t->field === $field)
                    ?->value;

                if ($fallbackValue !== null && $fallbackValue !== '') {
                    return $fallbackValue;
                }
            }
        }

        return $fallback;
    }

    public function setTranslation(string $field, string $value, Language|int|string $language, bool $machineTranslated = false): void
    {
        $languageId = $language instanceof Language
            ? $language->id
            : (is_numeric($language)
                ? (int) $language
                : Language::query()->where('code', $language)->value('id'));

        $this->translates()->updateOrCreate(
            [
                'language_id' => $languageId,
                'field' => $field,
            ],
            [
                'value' => $value,
                'is_machine_translated' => $machineTranslated,
            ]
        );

        $this->unsetRelation('translates');
    }

    protected function resolveLanguage(?string $languageCode): Language
    {
        if ($languageCode) {
            return Language::query()->where('code', $languageCode)->firstOrFail();
        }

        return app(CurrentLanguage::class)->get();
    }
}
