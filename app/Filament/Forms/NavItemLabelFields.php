<?php

namespace App\Filament\Forms;

use App\Models\Language;
use Filament\Forms\Components\TextInput;

final class NavItemLabelFields
{
    /**
     * @return list<TextInput>
     */
    public static function make(string $prefix = 'labels'): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Language $language) => TextInput::make("{$prefix}.{$language->code}")
                ->label('Подпись ('.strtoupper($language->code).')')
                ->maxLength(120)
                ->required($language->code === config('shop.default_language', 'pl')))
            ->all();
    }
}
