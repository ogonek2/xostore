<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSettings extends Model
{
    protected $fillable = [
        'show_category_showcase',
        'show_trending_section',
        'show_promotions_section',
        'show_new_arrivals_section',
        'show_banners_section',
        'category_showcase',
    ];

    protected function casts(): array
    {
        return [
            'show_category_showcase' => 'boolean',
            'show_trending_section' => 'boolean',
            'show_promotions_section' => 'boolean',
            'show_new_arrivals_section' => 'boolean',
            'show_banners_section' => 'boolean',
            'category_showcase' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'show_category_showcase' => true,
                'show_trending_section' => true,
                'show_promotions_section' => true,
                'show_new_arrivals_section' => true,
                'show_banners_section' => true,
            ],
        );
    }
}
