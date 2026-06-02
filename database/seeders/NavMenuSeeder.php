<?php

namespace Database\Seeders;

use App\Models\NavMenu;
use Illuminate\Database\Seeder;

class NavMenuSeeder extends Seeder
{
    public function run(): void
    {
        NavMenu::query()->updateOrCreate(
            ['code' => 'header'],
            [
                'name' => 'Шапка сайта',
                'is_active' => true,
            ],
        );
    }
}
