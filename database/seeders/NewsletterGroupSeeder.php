<?php

namespace Database\Seeders;

use App\Models\NewsletterGroup;
use Illuminate\Database\Seeder;

class NewsletterGroupSeeder extends Seeder
{
    public function run(): void
    {
        NewsletterGroup::query()->updateOrCreate(
            ['slug' => 'website'],
            [
                'name' => 'Подписки с сайта',
                'description' => 'Автоматически при подписке в футере магазина',
                'is_active' => true,
                'sort_order' => 0,
            ],
        );
    }
}
