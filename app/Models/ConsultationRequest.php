<?php

namespace App\Models;

use App\Enums\ConsultationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationRequest extends Model
{
    protected $fillable = [
        'status',
        'locale',
        'name',
        'email',
        'phone',
        'product_id',
        'message',
        'preferred_at',
        'admin_notes',
        'ip',
        'user_agent',
        'referer',
        'submission_source',
    ];

    protected function casts(): array
    {
        return [
            'status' => ConsultationStatus::class,
            'preferred_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
