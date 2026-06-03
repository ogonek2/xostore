<?php

namespace App\Filament\Resources\AdminUsers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class AdminUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Учётная запись')
                ->description('Доступ в панель /admin только при включённом «Доступ в админку».')
                ->schema([
                    TextInput::make('name')
                        ->label('Имя')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('password')
                        ->label('Пароль')
                        ->password()
                        ->revealable()
                        ->autocomplete('new-password')
                        ->rule(Password::defaults())
                        ->confirmed()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn ($livewire): bool => $livewire instanceof CreateRecord)
                        ->helperText(fn ($livewire): ?string => $livewire instanceof EditRecord
                            ? 'Оставьте пустым, чтобы не менять пароль.'
                            : null),
                    TextInput::make('password_confirmation')
                        ->label('Подтверждение пароля')
                        ->password()
                        ->revealable()
                        ->autocomplete('new-password')
                        ->dehydrated(false)
                        ->required(fn (Get $get, $livewire): bool => $livewire instanceof CreateRecord || filled($get('password'))),
                    Toggle::make('is_admin')
                        ->label('Доступ в админку')
                        ->default(true)
                        ->helperText('Снимите галочку, чтобы заблокировать вход в панель без удаления пользователя.'),
                ])
                ->columns(1)
                ->columnSpanFull(),
        ]);
    }
}
