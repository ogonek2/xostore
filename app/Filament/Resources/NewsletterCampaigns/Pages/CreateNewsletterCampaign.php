<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Enums\NewsletterCampaignStatus;
use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateNewsletterCampaign extends CreateRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $data['status'] ?? NewsletterCampaignStatus::Draft->value;
        $data['created_by'] = Auth::id();

        return $data;
    }
}
