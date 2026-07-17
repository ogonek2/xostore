<?php

namespace App\Http\Middleware;

use App\Support\Payments\BridgeSignature;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class VerifyPaymentBridgeSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $timestamp = (string) $request->header('X-Bridge-Timestamp', '');
        $nonce = (string) $request->header('X-Bridge-Nonce', '');
        $signature = (string) $request->header('X-Bridge-Signature', '');
        $secret = (string) config('services.payment_bridge.inbound_secret');
        $window = max(1, (int) config('services.payment_bridge.time_window', 300));

        if (
            $secret === ''
            || ! ctype_digit($timestamp)
            || abs(time() - (int) $timestamp) > $window
            || ! preg_match('/^[A-Za-z0-9-]{16,128}$/', $nonce)
            || ! preg_match('/^[a-f0-9]{64}$/', $signature)
        ) {
            return $this->unauthorized();
        }

        $expected = BridgeSignature::sign(
            $secret,
            $timestamp,
            $nonce,
            $request->method(),
            $request->getPathInfo(),
            $request->getContent(),
        );

        if (! hash_equals($expected, $signature)) {
            return $this->unauthorized();
        }

        try {
            DB::transaction(function () use ($nonce, $window): void {
                DB::table('bridge_nonces')->where('expires_at', '<', now())->delete();
                DB::table('bridge_nonces')->insert([
                    'nonce' => $nonce,
                    'direction' => 'inbound',
                    'expires_at' => now()->addSeconds($window),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'Bridge nonce has already been used.'], 409);
        }

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['message' => 'Invalid bridge signature.'], 401);
    }
}
