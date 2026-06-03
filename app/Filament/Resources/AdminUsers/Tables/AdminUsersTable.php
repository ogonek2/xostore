<?php

namespace App\Filament\Resources\AdminUsers\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AdminUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (User $record): void {
                        if ($record->getKey() === Auth::id()) {
                            Notification::make()
                                ->title('Нельзя удалить себя')
                                ->body('Выйдите под другим администратором или попросите коллегу.')
                                ->danger()
                                ->send();

                            throw new \Filament\Support\Exceptions\Halt;
                        }

                        if (User::query()->where('is_admin', true)->count() <= 1) {
                            Notification::make()
                                ->title('Нельзя удалить последнего администратора')
                                ->danger()
                                ->send();

                            throw new \Filament\Support\Exceptions\Halt;
                        }
                    }),
            ]);
    }
}
