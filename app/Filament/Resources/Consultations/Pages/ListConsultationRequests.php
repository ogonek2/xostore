<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\ConsultationRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListConsultationRequests extends ListRecords
{
    protected static string $resource = ConsultationRequestResource::class;
}
