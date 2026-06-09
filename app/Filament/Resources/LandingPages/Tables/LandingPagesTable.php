<?php

namespace App\Filament\Resources\LandingPages\Tables;

use App\Filament\Resources\LandingPages\LandingPageResource;
use App\Models\LandingPage;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LandingPagesTable
{
    public static function configure(Table $table): Table
    {
        $defaultLocale = (string) config('shop.default_language', 'pl');

        return $table
            ->recordUrl(fn (LandingPage $record): string => LandingPageResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('code')
                    ->label('Код')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->getStateUsing(fn (LandingPage $record): string => $record->translate('name', $defaultLocale) ?? $record->code),
                TextColumn::make('slug')
                    ->label('URL slug')
                    ->getStateUsing(fn (LandingPage $record): ?string => $record->translate('slug', $defaultLocale))
                    ->placeholder('—'),
                TextColumn::make('blocks_count')
                    ->label('Блоков')
                    ->counts('blocks'),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Обновлена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Активна'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
