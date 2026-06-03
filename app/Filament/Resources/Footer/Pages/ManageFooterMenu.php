<?php

namespace App\Filament\Resources\Footer\Pages;

use App\Filament\Resources\Footer\FooterMenuResource;
use App\Models\NavMenu;
use Filament\Resources\Pages\EditRecord;

class ManageFooterMenu extends EditRecord
{
    protected static string $resource = FooterMenuResource::class;

    protected static ?string $title = 'Колонки ссылок в футере';

    public function mount(int|string|null $record = null): void
    {
        $menu = NavMenu::query()->firstOrCreate(
            ['code' => 'footer'],
            ['name' => 'Футер', 'is_active' => true],
        );

        parent::mount($menu->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
