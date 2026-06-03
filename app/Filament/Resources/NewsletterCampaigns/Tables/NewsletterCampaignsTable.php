<?php

namespace App\Filament\Resources\NewsletterCampaigns\Tables;

use App\Enums\NewsletterCampaignStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsletterCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Тема')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('group.name')
                    ->label('Группа')
                    ->placeholder('Все подписанные'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof NewsletterCampaignStatus
                            ? $state->label()
                            : NewsletterCampaignStatus::tryFrom((string) $state)?->label() ?? $state
                    ),
                TextColumn::make('recipients_count')
                    ->label('Получателей')
                    ->sortable(),
                TextColumn::make('sent_count')
                    ->label('Отправлено')
                    ->sortable(),
                TextColumn::make('failed_count')
                    ->label('Ошибки')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Завершена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(collect(NewsletterCampaignStatus::cases())->mapWithKeys(
                        fn (NewsletterCampaignStatus $status) => [$status->value => $status->label()]
                    )),
            ]);
    }
}
