<?php

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (User $record): void {
                    if ($record->getKey() === Auth::id()) {
                        Notification::make()
                            ->title('Нельзя удалить себя')
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
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! ($data['is_admin'] ?? false)) {
            /** @var User $record */
            $record = $this->getRecord();

            if ($record->getKey() === Auth::id()) {
                Notification::make()
                    ->title('Нельзя отключить доступ себе')
                    ->body('Попросите другого администратора изменить вашу учётную запись.')
                    ->danger()
                    ->send();

                $data['is_admin'] = true;
            }

            if (User::query()->where('is_admin', true)->whereKeyNot($record->getKey())->doesntExist()) {
                Notification::make()
                    ->title('Нельзя отключить последнего администратора')
                    ->danger()
                    ->send();

                $data['is_admin'] = true;
            }
        }

        return $data;
    }
}
