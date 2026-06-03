<?php

namespace App\Filament\Widgets\Dashboard\Concerns;

use App\Support\Analytics\ShopAnalytics;

trait InteractsWithShopAnalytics
{
    protected function analytics(): ShopAnalytics
    {
        return app(ShopAnalytics::class);
    }

    /**
     * @return list<string>
     */
    protected function chartPalette(): array
    {
        return ['#c9a962', '#a8894a', '#d4bc82', '#6b5a3e', '#e8dcc0', '#3d3428', '#f0e6d0'];
    }
}
