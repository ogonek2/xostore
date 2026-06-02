<?php

namespace App\Filament\Resources\Banners\Tables;

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
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('link_url')
                    ->label('Ссылка')
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
