<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Products\Concerns\ManagesProductRecord;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Support\Shop\ProductVariantColorSync;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    use HandlesTranslations;
    use ManagesProductRecord {
        persistProductTranslations as persistProductTranslationsFromRecord;
    }

    protected static string $resource = ProductResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Основное';
    }

    protected function getTranslationConfigKey(): string
    {
        return 'product';
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->isDraftProduct($this->getRecord())) {
            Notification::make()
                ->title('Новый товар')
                ->body('Заполните основные данные и сохраните. Галерея, размеры и связи доступны на соседних вкладках.')
                ->info()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = $this->fillTranslationFormData($data);

        $primary = $this->getRecord()->images()
            ->where('is_primary', true)
            ->first()
            ?? $this->getRecord()->images()->orderBy('sort_order')->first();

        $data['primary_image'] = $primary?->path;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->normalizeProductSlugs($data);

        $this->pendingPrimaryImage = $data['primary_image'] ?? null;
        unset($data['primary_image']);

        return $this->captureTranslationFormData($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Product $record */
        $record = parent::handleRecordUpdate($record, $data);

        $this->persistProductTranslationsFromRecord($record);

        return $record;
    }

    protected function afterSave(): void
    {
        $product = $this->getRecord();
        $this->syncPrimaryImage($product);
        ProductVariantColorSync::syncProductVariants($product);
        $this->notifyAutoTranslationResult();
    }

    protected function notifyAutoTranslationResult(): void
    {
        if ($this->lastAutoTranslatedCount > 0) {
            $this->record->refresh();
            $this->record->load('translates');
            $this->fillForm();

            Notification::make()
                ->title('Автоперевод выполнен')
                ->body("Заполнено полей на других языках: {$this->lastAutoTranslatedCount}.")
                ->success()
                ->send();

            return;
        }

        if ($this->lastAutoTranslateFailed) {
            Notification::make()
                ->title('Автоперевод недоступен')
                ->body('Проверьте интернет и доступ к API перевода. Польский текст сохранён, остальные языки нужно заполнить вручную.')
                ->warning()
                ->send();
        }
    }
}
