<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PayUNotificationController extends Controller
{
    public function __invoke(Request $request, PaymentEventService $events): JsonResponse
    {
        $body = $request->getContent();
        $header = (string) ($request->header('OpenPayu-Signature')
            ?: $request->header('X-OpenPayU-Signature', ''));
        $signature = $this->parseSignature($header);
        $secondKey = (string) config('services.payu.second_key');
        $expected = md5($body.$secondKey);

        if ($secondKey === ''
            || strtolower($signature['algorithm'] ?? '') !== 'md5'
            || ! preg_match('/\A[0-9a-fA-F]{32}\z/', (string) ($signature['signature'] ?? ''))
            || ! hash_equals($expected, strtolower((string) ($signature['signature'] ?? '')))) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = json_decode($body, true);
        if (! is_array($payload) || ! is_array($payload['order'] ?? null)) {
            return response()->json(['message' => 'Invalid notification.'], 400);
        }

        try {
            $events->accept($payload['order'], hash('sha256', $body));
        } catch (ValidationException) {
            return response()->json(['message' => 'Notification order mismatch.'], 422);
        }

        return response()->json(['status' => 'ok']);
    }

    private function parseSignature(string $header): array
    {
        $parts = [];
        foreach (explode(';', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key !== '' && $value !== null) {
                $parts[strtolower($key)] = trim($value);
            }
        }

        return $parts;
    }
}
