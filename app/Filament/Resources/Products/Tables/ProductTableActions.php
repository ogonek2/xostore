<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class ProductTableActions
{
    /**
     * @return array<int, mixed>
     */
    public static function recordActions(): array
    {
        return [
            EditAction::make(),
            ActionGroup::make(static::quickStatusActions())
                ->label('Статус')
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->color('gray')
                ->button(),
            ActionGroup::make(static::quickFlagActions())
                ->label('Флаги')
                ->icon(Heroicon::OutlinedFlag)
                ->color('gray')
                ->button(),
            DeleteAction::make(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('publish')
                    ->label('Опубликовать')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (BulkAction $action) => static::bulkApplyStatus($action, ProductStatus::Published))
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Выбранные товары опубликованы'),
                BulkAction::make('draft')
                    ->label('В черновик')
                    ->icon(Heroicon::OutlinedDocument)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn (BulkAction $action) => static::bulkApplyStatus($action, ProductStatus::Draft))
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Выбранные товары переведены в черновик'),
                BulkAction::make('archive')
                    ->label('В архив')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (BulkAction $action) => static::bulkApplyStatus($action, ProductStatus::Archived))
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Выбранные товары отправлены в архив'),
                BulkAction::make('enableReadyToShip')
                    ->label('Включить «В наличии»')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('info')
                    ->action(fn (BulkAction $action) => static::bulkUpdateFlag($action, 'is_ready_to_ship', true))
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Флаг «В наличии» включён'),
                BulkAction::make('disableReadyToShip')
                    ->label('Выключить «В наличии»')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('gray')
                    ->action(fn (BulkAction $action) => static::bulkUpdateFlag($action, 'is_ready_to_ship', false))
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Флаг «В наличии» выключен'),
                BulkAction::make('enableFeatured')
                    ->label('Рекомендованный: вкл')
                    ->icon(Heroicon::OutlinedStar)
                    ->action(fn (BulkAction $action) => static::bulkUpdateFlag($action, 'is_featured', true))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('disableFeatured')
                    ->label('Рекомендованный: выкл')
                    ->icon(Heroicon::OutlinedStar)
                    ->action(fn (BulkAction $action) => static::bulkUpdateFlag($action, 'is_featured', false))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('enableNew')
                    ->label('Новинка: вкл')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->action(fn (BulkAction $action) => static::bulkUpdateFlag($action, 'is_new', true))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('disableNew')
                    ->label('Новинка: выкл')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->action(fn (BulkAction $action) => static::bulkUpdateFlag($action, 'is_new', false))
                    ->deselectRecordsAfterCompletion(),
                DeleteBulkAction::make()
                    ->label('Удалить выбранные'),
                ForceDeleteBulkAction::make()
                    ->label('Удалить навсегда'),
                RestoreBulkAction::make()
                    ->label('Восстановить'),
            ])
                ->label('Действия с выбранными'),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected static function quickStatusActions(): array
    {
        return [
            Action::make('publish')
                ->label('Опубликовать')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->visible(fn (Product $record): bool => static::recordStatus($record) !== ProductStatus::Published)
                ->action(fn (Product $record) => static::applyStatus($record, ProductStatus::Published)),
            Action::make('draft')
                ->label('В черновик')
                ->icon(Heroicon::OutlinedDocument)
                ->color('gray')
                ->visible(fn (Product $record): bool => static::recordStatus($record) !== ProductStatus::Draft)
                ->action(fn (Product $record) => static::applyStatus($record, ProductStatus::Draft)),
            Action::make('archive')
                ->label('В архив')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('warning')
                ->visible(fn (Product $record): bool => static::recordStatus($record) !== ProductStatus::Archived)
                ->action(fn (Product $record) => static::applyStatus($record, ProductStatus::Archived)),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected static function quickFlagActions(): array
    {
        return [
            Action::make('toggleReadyToShip')
                ->label(fn (Product $record): string => $record->is_ready_to_ship ? 'Выключить «В наличии»' : 'Включить «В наличии»')
                ->icon(Heroicon::OutlinedTruck)
                ->action(fn (Product $record) => $record->update(['is_ready_to_ship' => ! $record->is_ready_to_ship])),
            Action::make('toggleFeatured')
                ->label(fn (Product $record): string => $record->is_featured ? 'Снять «Рекомендованный»' : 'Сделать рекомендованным')
                ->icon(Heroicon::OutlinedStar)
                ->action(fn (Product $record) => $record->update(['is_featured' => ! $record->is_featured])),
            Action::make('toggleNew')
                ->label(fn (Product $record): string => $record->is_new ? 'Снять «Новинка»' : 'Сделать новинкой')
                ->icon(Heroicon::OutlinedSparkles)
                ->action(fn (Product $record) => $record->update(['is_new' => ! $record->is_new])),
        ];
    }

    protected static function bulkApplyStatus(BulkAction $action, ProductStatus $status): void
    {
        $action->process(static function (BulkAction $action, EloquentCollection $records) use ($status): void {
            $records->each(function (Product $product) use ($status): void {
                static::applyStatus($product, $status);
            });
        });
    }

    protected static function bulkUpdateFlag(BulkAction $action, string $field, bool $value): void
    {
        $action->process(static function (BulkAction $action, EloquentCollection $records) use ($field, $value): void {
            $records->each(function (Product $product) use ($field, $value): void {
                $product->update([$field => $value]);
            });
        });
    }

    protected static function applyStatus(Product $product, ProductStatus $status): void
    {
        $payload = ['status' => $status->value];

        if ($status === ProductStatus::Published && ! $product->published_at) {
            $payload['published_at'] = now();
        }

        $product->update($payload);
    }

    protected static function recordStatus(Product $record): ?ProductStatus
    {
        if ($record->status instanceof ProductStatus) {
            return $record->status;
        }

        return ProductStatus::tryFrom((string) $record->status);
    }
}
