<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroBannerItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'hero_banner_section_id',
        'image_path',
        'link_url',
        'button_url',
        'text_position',
        'text_color',
        'overlay_opacity',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'overlay_opacity' => 'integer',
        ];
    }

    public function translatableFields(): array
    {
        return config('shop.hero_banner_item.translatable_fields', [
            'title',
            'subtitle',
            'button_label',
        ]);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(HeroBannerSection::class, 'hero_banner_section_id');
    }
}
