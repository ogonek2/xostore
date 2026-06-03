<?php

namespace App\Models;

use App\Enums\NewsletterCampaignStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body_html',
        'body_text',
        'status',
        'newsletter_group_id',
        'scheduled_at',
        'started_at',
        'completed_at',
        'recipients_count',
        'sent_count',
        'failed_count',
        'skipped_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => NewsletterCampaignStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(NewsletterGroup::class, 'newsletter_group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }

    public function canSend(): bool
    {
        return $this->status === NewsletterCampaignStatus::Draft;
    }

    public function isLocked(): bool
    {
        return $this->status === NewsletterCampaignStatus::Sent;
    }
}
