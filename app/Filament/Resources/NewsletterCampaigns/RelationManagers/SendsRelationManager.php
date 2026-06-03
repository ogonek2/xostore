<?php

namespace App\Filament\Resources\NewsletterCampaigns\RelationManagers;

use App\Enums\NewsletterSendStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SendsRelationManager extends RelationManager
{
    protected static string $relationship = 'sends';

    protected static ?string $title = 'Отправки';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscriber.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Результат')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $status = $state instanceof NewsletterSendStatus
                            ? $state
                            : NewsletterSendStatus::tryFrom((string) $state);

                        return match ($status) {
                            NewsletterSendStatus::Sent => 'Доставлено',
                            NewsletterSendStatus::Failed => 'Ошибка',
                            default => '—',
                        };
                    }),
                TextColumn::make('sent_at')
                    ->label('Отправлено')
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('error_message')
                    ->label('Ошибка')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc');
    }
}
