<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopVisitorSession extends Model
{
    protected $fillable = [
        'token',
        'ip_address',
        'user_agent',
        'landing_path',
        'referrer',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShopEvent::class);
    }
}
