<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterSubscribeRequest;
use App\Services\Newsletter\NewsletterSubscriptionService;
use Illuminate\Http\JsonResponse;

class NewsletterSubscriptionController extends Controller
{
    public function store(
        NewsletterSubscribeRequest $request,
        string $locale,
        NewsletterSubscriptionService $subscriptionService,
    ): JsonResponse {
        $result = $subscriptionService->subscribe(
            email: $request->validated('email'),
            locale: $locale,
            name: $request->validated('name'),
            source: 'website',
        );

        return response()->json([
            'message' => __('shop.footer.newsletter.success'),
            'created' => $result['created'],
            'reactivated' => $result['reactivated'],
        ]);
    }
}
