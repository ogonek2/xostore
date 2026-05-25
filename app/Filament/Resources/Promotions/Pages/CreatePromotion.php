<?php

namespace App\Filament\Resources\Promotions\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Promotions\PromotionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotion extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = PromotionResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'promotion';
    }
}
