<?php

namespace App\Filament\Forms;

use App\Models\Language;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class TranslationTabs
{
    /**
     * @param  list<string>|null  $onlyFields
     */
    public static function make(string $configKey, ?string $heading = null, ?array $onlyFields = null): Tabs
    {
        $fields = $onlyFields ?? config("shop.{$configKey}.translatable_fields", ['name', 'slug']);
        $requiredFields = config("shop.{$configKey}.required_translation_fields");
        $languages = Language::query()->where('is_active', true)->orderBy('sort_order')->get();

        return Tabs::make($heading ?? __('admin.translations'))
            ->tabs(
                $languages->map(function (Language $language) use ($fields, $requiredFields) {
                    $schema = collect($fields)->map(
                        fn (string $field) => static::fieldComponent($field, $language->code, $requiredFields)
                    )->all();

                    return Tab::make($language->name)
                        ->schema($schema);
                })->all()
            )
            ->columnSpanFull();
    }

    /**
     * @param  list<string>|null  $requiredFields
     */
    protected static function fieldComponent(string $field, string $locale, ?array $requiredFields = null): TextInput|Textarea|RichEditor
    {
        $name = "trans_{$locale}_{$field}";
        $label = __('admin.fields.'.$field, [], 'ru');
        $defaultLocale = (string) config('shop.default_language', 'pl');
        $primaryFields = $requiredFields ?? ['name', 'title', 'label'];
        $isRequiredOnLocale = in_array($field, $primaryFields, true) && $locale === $defaultLocale;

        if ($label === 'admin.fields.'.$field) {
            $label = ucfirst(str_replace('_', ' ', $field));
        }

        return match ($field) {
            'description', 'content' => RichEditor::make($name)
                ->label($label)
                ->columnSpanFull()
                ->extraInputAttributes(['autocomplete' => 'off']),
            'short_description', 'meta_description', 'tailoring_description', 'fit_description', 'fabric_description', 'caption' => Textarea::make($name)
                ->label($label)
                ->rows(3)
                ->columnSpanFull()
                ->extraInputAttributes(['autocomplete' => 'off']),
            'link_url' => TextInput::make($name)
                ->label($label)
                ->maxLength(500)
                ->placeholder('produkty или https://…')
                ->helperText('Относительный путь без /pl/ — подставится язык витрины.')
                ->extraInputAttributes(['autocomplete' => 'off']),
            'slug' => TextInput::make($name)
                ->label($label)
                ->maxLength(255)
                ->helperText('Генерируется автоматически из названия, если оставить пустым')
                ->extraInputAttributes(['autocomplete' => 'off'])
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Set $set) use ($name): void {
                    if (! is_string($state) || trim($state) === '') {
                        return;
                    }

                    $set($name, Str::slug($state));
                }),
            default => TextInput::make($name)
                ->label($label)
                ->required($isRequiredOnLocale)
                ->extraInputAttributes(['autocomplete' => 'off'])
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Set $set, Get $get) use ($field, $locale): void {
                    if (! in_array($field, ['name', 'title', 'label'], true)) {
                        return;
                    }

                    $slugField = "trans_{$locale}_slug";
                    $existingSlug = $get($slugField);

                    if (is_string($existingSlug) && trim($existingSlug) !== '') {
                        return;
                    }

                    if (! is_string($state) || trim($state) === '') {
                        return;
                    }

                    $set($slugField, Str::slug($state));
                }),
        };
    }
}
