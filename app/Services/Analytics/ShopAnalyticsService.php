<?php

namespace App\Services\Analytics;

use App\Enums\ShopEventType;
use App\Models\ShopEvent;
use App\Models\ShopVisitorSession;
use Illuminate\Support\Str;

class ShopAnalyticsService
{
    public function resolveSession(): ShopVisitorSession
    {
        $token = session('shop_visitor_token');

        if (! $token) {
            $token = (string) Str::uuid();
            session(['shop_visitor_token' => $token]);
        }

        return ShopVisitorSession::query()->firstOrCreate(
            ['token' => $token],
            [
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 500, ''),
                'landing_path' => Str::limit((string) request()->path(), 500, ''),
                'referrer' => Str::limit((string) request()->header('referer'), 500, ''),
                'last_activity_at' => now(),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function track(
        ShopEventType $type,
        ?int $productId = null,
        ?int $categoryId = null,
        ?int $variantId = null,
        array $payload = [],
    ): void {
        $session = $this->resolveSession();
        $session->update(['last_activity_at' => now()]);

        ShopEvent::query()->create([
            'shop_visitor_session_id' => $session->id,
            'event_type' => $type,
            'path' => Str::limit((string) request()->path(), 500, ''),
            'product_id' => $productId,
            'category_id' => $categoryId,
            'product_variant_id' => $variantId,
            'payload' => $payload ?: null,
            'created_at' => now(),
        ]);
    }
}
