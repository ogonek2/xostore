<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

final class AdminTableColumns
{
    public static function plTranslation(string $field = 'name', string $label = 'Название (PL)'): TextColumn
    {
        return TextColumn::make('pl_'.$field)
            ->label($label)
            ->getStateUsing(function (Model $record) use ($field): ?string {
                $record->loadMissing('translates');

                if (! method_exists($record, 'translate')) {
                    return null;
                }

                return $record->translate($field, 'pl')
                    ?? ($record->getAttribute('code') ?? null);
            });
    }

    public static function plTitle(string $label = 'Заголовок (PL)'): TextColumn
    {
        return static::plTranslation('title', $label);
    }
}
