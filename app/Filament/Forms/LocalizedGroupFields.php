<?php

namespace App\Filament\Forms;

use App\Models\Language;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

final class LocalizedGroupFields
{
    /**
     * @param  list<array{name: string, label: string, type?: string, maxLength?: int, rows?: int}>  $fields
     * @return list<Section>
     */
    public static function sections(string $group, array $fields): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Language $language) use ($group, $fields) {
                $schema = [];

                foreach ($fields as $field) {
                    $name = "{$group}.{$language->code}.{$field['name']}";
                    $component = ($field['type'] ?? 'text') === 'textarea'
                        ? Textarea::make($name)->rows($field['rows'] ?? 3)
                        : TextInput::make($name)->maxLength($field['maxLength'] ?? 255);

                    $component->label($field['label'].' ('.strtoupper($language->code).')');

                    if ($language->code === config('shop.default_language', 'pl')) {
                        $component->required();
                    }

                    $schema[] = $component;
                }

                return Section::make(strtoupper($language->code))
                    ->schema($schema)
                    ->columns(2)
                    ->compact();
            })
            ->all();
    }
}
