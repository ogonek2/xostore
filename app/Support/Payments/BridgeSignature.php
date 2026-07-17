<?php

namespace App\Support\Payments;

final class BridgeSignature
{
    public static function canonical(
        string $timestamp,
        string $nonce,
        string $method,
        string $path,
        string $rawBody,
    ): string {
        $path = '/'.ltrim($path, '/');

        return implode("\n", [
            $timestamp,
            $nonce,
            strtoupper($method),
            $path,
            hash('sha256', $rawBody),
        ]);
    }

    public static function sign(
        string $secret,
        string $timestamp,
        string $nonce,
        string $method,
        string $path,
        string $rawBody,
    ): string {
        return hash_hmac('sha256', self::canonical($timestamp, $nonce, $method, $path, $rawBody), $secret);
    }
}
