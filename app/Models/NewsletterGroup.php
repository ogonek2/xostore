<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsletterGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterSubscriber::class, 'newsletter_group_subscriber')
            ->withTimestamps();
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(NewsletterCampaign::class);
    }

    protected static function booted(): void
    {
        static::saving(function (NewsletterGroup $group): void {
            if (filled($group->slug)) {
                return;
            }

            $group->slug = Str::slug($group->name);
        });
    }
}
