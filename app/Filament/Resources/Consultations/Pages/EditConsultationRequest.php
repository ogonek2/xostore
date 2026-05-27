<?php

namespace App\Filament\Resources\Consultations\Pages;

use App\Filament\Resources\Consultations\ConsultationRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditConsultationRequest extends EditRecord
{
    protected static string $resource = ConsultationRequestResource::class;
}
