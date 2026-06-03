<?php

namespace App\Models;

use App\Enums\NewsletterSubscriberStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'name',
        'locale',
        'status',
        'source',
        'unsubscribe_token',
        'subscribed_at',
        'unsubscribed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => NewsletterSubscriberStatus::class,
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterGroup::class, 'newsletter_group_subscriber')
            ->withTimestamps();
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }

    public function isMailable(): bool
    {
        return $this->status === NewsletterSubscriberStatus::Subscribed;
    }

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $subscriber): void {
            if (! $subscriber->unsubscribe_token) {
                $subscriber->unsubscribe_token = Str::random(48);
            }
        });
    }
}
