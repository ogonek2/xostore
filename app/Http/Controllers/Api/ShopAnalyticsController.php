<?php

namespace App\Http\Controllers\Api;

use App\Enums\ShopEventType;
use App\Http\Controllers\Controller;
use App\Services\Analytics\ShopAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopAnalyticsController extends Controller
{
    public function __construct(
        protected ShopAnalyticsService $analytics,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => ['required', 'string'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'path' => ['nullable', 'string', 'max:500'],
            'payload' => ['nullable', 'array'],
        ]);

        $type = ShopEventType::tryFrom($validated['event']);

        if (! $type) {
            return response()->json(['message' => 'Unknown event'], 422);
        }

        if (! empty($validated['path'])) {
            $request->merge(['path' => $validated['path']]);
        }

        $this->analytics->track(
            $type,
            $validated['product_id'] ?? null,
            $validated['category_id'] ?? null,
            $validated['variant_id'] ?? null,
            $validated['payload'] ?? [],
        );

        return response()->json(['ok' => true]);
    }
}
