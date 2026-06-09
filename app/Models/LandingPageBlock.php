<?php

namespace App\Models;

use App\Enums\LandingPageBlockType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageBlock extends Model
{
    use HasTranslations;

    protected $fillable = [
        'landing_page_id',
        'type',
        'sort_order',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => LandingPageBlockType::class,
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function typeEnum(): LandingPageBlockType
    {
        $type = $this->type;

        return $type instanceof LandingPageBlockType
            ? $type
            : LandingPageBlockType::from((string) $type);
    }
}
