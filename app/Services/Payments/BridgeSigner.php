<?php

namespace App\Services\Payments;

class BridgeSigner
{
    public function canonical(string $timestamp, string $nonce, string $method, string $path, string $body): string
    {
        $path = '/'.ltrim($path, '/');

        return implode("\n", [
            $timestamp,
            $nonce,
            strtoupper($method),
            $path,
            hash('sha256', $body),
        ]);
    }

    public function sign(
        string $secret,
        string $timestamp,
        string $nonce,
        string $method,
        string $path,
        string $body,
    ): string {
        return hash_hmac('sha256', $this->canonical($timestamp, $nonce, $method, $path, $body), $secret);
    }
}
