<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrokerPaymentIntent;
use Illuminate\Http\RedirectResponse;

class PayUReturnController extends Controller
{
    public function __invoke(string $brokerPaymentId, string $token): RedirectResponse
    {
        $intent = BrokerPaymentIntent::query()
            ->where('broker_payment_id', $brokerPaymentId)
            ->firstOrFail();

        abort_unless(hash_equals($intent->return_token, $token), 404);

        $host = parse_url($intent->return_url, PHP_URL_HOST);
        $scheme = parse_url($intent->return_url, PHP_URL_SCHEME);
        abort_unless(
            $scheme === 'https'
            && is_string($host)
            && in_array(strtolower($host), config('services.payment_bridge.allowed_return_hosts', []), true),
            400,
            'Invalid return URL.',
        );

        return redirect()->away($intent->return_url);
    }
}
