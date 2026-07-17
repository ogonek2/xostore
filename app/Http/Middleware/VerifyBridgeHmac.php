<?php

namespace App\Http\Middleware;

use App\Models\BridgeNonce;
use App\Services\Payments\BridgeSigner;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBridgeHmac
{
    public function __construct(private readonly BridgeSigner $signer) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('services.payment_bridge.enabled')) {
            return response()->json(['message' => 'Payment bridge is disabled.'], 503);
        }

        $timestamp = (string) $request->header('X-Bridge-Timestamp', '');
        $nonce = (string) $request->header('X-Bridge-Nonce', '');
        $signature = strtolower((string) $request->header('X-Bridge-Signature', ''));
        $secret = (string) config('services.payment_bridge.inbound_secret', '');
        $window = (int) config('services.payment_bridge.time_window', 300);

        if (! ctype_digit($timestamp)
            || ! preg_match('/\A[A-Za-z0-9_-]{16,128}\z/', $nonce)
            || ! preg_match('/\A[0-9a-f]{64}\z/', $signature)
            || $secret === ''
            || abs(time() - (int) $timestamp) > $window) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $expected = $this->signer->sign(
            $secret,
            $timestamp,
            $nonce,
            $request->method(),
            $request->getPathInfo(),
            $request->getContent(),
        );

        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            BridgeNonce::query()->create([
                'nonce' => $nonce,
                'expires_at' => now()->addSeconds($window),
            ]);
        } catch (QueryException) {
            return response()->json(['message' => 'Replay detected.'], 409);
        }

        if (random_int(1, 100) === 1) {
            BridgeNonce::query()->where('expires_at', '<', now())->delete();
        }

        return $next($request);
    }
}
