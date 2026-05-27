<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'code' => 'pl',
                'name' => 'Polski',
                'locale' => 'pl_PL',
                'flag' => 'pl',
                'is_default' => true,
                'is_active' => true,
                'auto_translate_on_create' => false,
                'sort_order' => 1,
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'locale' => 'en',
                'flag' => 'gb',
                'is_default' => false,
                'is_active' => true,
                'auto_translate_on_create' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($languages as $data) {
            Language::query()->updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
