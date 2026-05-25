<?php

namespace App\Filament\Forms;

use App\Models\Language;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class TranslationTabs
{
    public static function make(string $configKey, string $heading = 'Tłumaczenia'): Tabs
    {
        $fields = config("shop.{$configKey}.translatable_fields", ['name', 'slug']);
        $languages = Language::query()->where('is_active', true)->orderBy('sort_order')->get();

        return Tabs::make($heading)
            ->tabs(
                $languages->map(function (Language $language) use ($fields) {
                    $schema = collect($fields)->map(
                        fn (string $field) => static::fieldComponent($field, $language->code)
                    )->all();

                    return Tab::make($language->name)
                        ->schema($schema);
                })->all()
            )
            ->columnSpanFull();
    }

    protected static function fieldComponent(string $field, string $locale): TextInput|Textarea|RichEditor
    {
        $name = "trans_{$locale}_{$field}";
        $label = match ($field) {
            'name' => 'Nazwa',
            'slug' => 'Slug (URL)',
            'title' => 'Tytuł',
            'subtitle' => 'Podtytuł',
            'cta_label' => 'Przycisk CTA',
            'short_description' => 'Krótki opis',
            'description' => 'Opis',
            'meta_title' => 'Meta tytuł',
            'meta_description' => 'Meta opis',
            default => ucfirst(str_replace('_', ' ', $field)),
        };

        return match ($field) {
            'description' => RichEditor::make($name)->label($label)->columnSpanFull(),
            'short_description', 'meta_description' => Textarea::make($name)->label($label)->rows(3)->columnSpanFull(),
            default => TextInput::make($name)
                ->label($label)
                ->required(in_array($field, ['name', 'title'], true))
                ->maxLength($field === 'slug' ? 255 : null),
        };
    }
}
