<?php

namespace App\Filament\Resources\Banners\Tables;

use App\Models\Banner;
use App\Support\Media\Media;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Изображение')
                    ->disk(Media::disk()),
                TextColumn::make('title')
                    ->label('Название')
                    ->getStateUsing(
                        fn (Banner $record): ?string => $record->translate(
                            'title',
                            (string) config('shop.default_language', 'pl'),
                        )
                    )
                    ->placeholder('—'),
                TextColumn::make('link_url')
                    ->label('Ссылка')
                    ->getStateUsing(
                        fn (Banner $record): ?string => $record->translate(
                            'link_url',
                            (string) config('shop.default_language', 'pl'),
                        )
                    )
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Активен'),
            ]);
    }
}
