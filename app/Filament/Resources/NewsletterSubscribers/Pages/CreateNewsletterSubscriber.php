<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Enums\NewsletterSubscriberStatus;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterSubscriber extends CreateRecord
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === NewsletterSubscriberStatus::Subscribed->value && empty($data['subscribed_at'])) {
            $data['subscribed_at'] = now();
        }

        return $data;
    }
}
