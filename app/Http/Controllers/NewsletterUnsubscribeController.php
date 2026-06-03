<?php

namespace App\Http\Controllers;

use App\Services\Newsletter\NewsletterSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterUnsubscribeController extends Controller
{
    public function __invoke(Request $request, string $token, NewsletterSubscriptionService $subscriptionService): View
    {
        $subscriber = $subscriptionService->unsubscribe($token);

        return view('newsletter.unsubscribe', [
            'success' => $subscriber !== null,
            'email' => $subscriber?->email,
        ]);
    }
}
