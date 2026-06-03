<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class ShopDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Дашборд';

    protected static ?string $title = 'Аналитика магазина';

    protected static ?int $navigationSort = -2;

    public function getTitle(): string | Htmlable
    {
        return 'Аналитика магазина';
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
