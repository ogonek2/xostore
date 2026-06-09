<?php

namespace App\Filament\Resources\LandingPages\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\LandingPages\LandingPageResource;
use App\Models\LandingPage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLandingPage extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = LandingPageResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'landing_page';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Настройки';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Открыть на сайте')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(function (LandingPage $record): ?string {
                    $locale = (string) config('shop.default_language', 'pl');
                    $slug = $record->translate('slug', $locale);

                    if (! $slug) {
                        return null;
                    }

                    return route('landing.show', ['locale' => $locale, 'landing' => $slug]);
                })
                ->openUrlInNewTab()
                ->visible(fn (LandingPage $record): bool => filled($record->translate('slug', config('shop.default_language', 'pl')))),
            DeleteAction::make(),
        ];
    }
}
