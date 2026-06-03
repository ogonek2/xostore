<?php

namespace App\Filament\Resources\NewsletterGroups\Pages;

use App\Filament\Resources\NewsletterGroups\NewsletterGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterGroup extends CreateRecord
{
    protected static string $resource = NewsletterGroupResource::class;
}
