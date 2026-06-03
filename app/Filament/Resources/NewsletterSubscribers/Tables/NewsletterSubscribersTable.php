<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use App\Enums\NewsletterSubscriberStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('locale')
                    ->label('Язык')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof NewsletterSubscriberStatus
                            ? $state->label()
                            : NewsletterSubscriberStatus::tryFrom((string) $state)?->label() ?? $state
                    ),
                TextColumn::make('groups.name')
                    ->label('Группы')
                    ->badge()
                    ->limitList(3),
                TextColumn::make('source')
                    ->label('Источник')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subscribed_at')
                    ->label('Подписка')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(collect(NewsletterSubscriberStatus::cases())->mapWithKeys(
                        fn (NewsletterSubscriberStatus $status) => [$status->value => $status->label()]
                    )),
                SelectFilter::make('groups')
                    ->label('Группа')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }
}
