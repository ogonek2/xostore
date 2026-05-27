<?php

namespace App\Filament\Resources\Consultations\Tables;

use App\Enums\ConsultationStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConsultationRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Имя')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn ($s) => $s instanceof ConsultationStatus ? $s->label() : $s),
                TextColumn::make('created_at')->label('Создана')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(collect(ConsultationStatus::cases())->mapWithKeys(
                    fn (ConsultationStatus $s) => [$s->value => $s->label()]
                )),
            ]);
    }
}
