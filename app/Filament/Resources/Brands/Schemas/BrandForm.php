<?php

namespace App\Filament\Resources\Brands\Schemas;

use App\Filament\Forms\TranslationTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('code')->label('Код')->required()->unique(ignoreRecord: true)->alphaDash(),
                Toggle::make('is_active')->label('Активен')->default(true),
                TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
            ]),
            TranslationTabs::make('brand'),
        ]);
    }
}
