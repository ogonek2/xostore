<?php

namespace App\Services\Translation;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoTranslator
{
    /** MyMemory free tier works best with shorter segments. */
    protected const MAX_SEGMENT_LENGTH = 450;

    public function translate(string $text, string $from, string $to, ?string $context = null): ?string
    {
        $text = trim($text);

        if ($text === '' || $from === $to) {
            return $text;
        }

        if (mb_strlen($text) > self::MAX_SEGMENT_LENGTH) {
            return $this->translateLongText($text, $from, $to, $context);
        }

        return $this->requestTranslation($text, $from, $to, $context);
    }

    protected function translateLongText(string $text, string $from, string $to, ?string $context): ?string
    {
        $chunks = mb_str_split($text, self::MAX_SEGMENT_LENGTH);
        $translated = [];

        foreach ($chunks as $chunk) {
            $part = $this->requestTranslation($chunk, $from, $to, $context);

            if ($part === null) {
                return null;
            }

            $translated[] = $part;
        }

        return trim(implode('', $translated));
    }

    protected function requestTranslation(string $text, string $from, string $to, ?string $context): ?string
    {
        try {
            $response = $this->httpClient()
                ->get('https://api.mymemory.translated.net/get', [
                    'q' => $text,
                    'langpair' => "{$from}|{$to}",
                ]);

            if (! $response->successful()) {
                Log::warning('Auto translation HTTP error', [
                    'from' => $from,
                    'to' => $to,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $translated = $response->json('responseData.translatedText');

            if (! is_string($translated) || trim($translated) === '') {
                return null;
            }

            $translated = html_entity_decode(trim($translated), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return $this->cleanupTranslation($text, $translated, $context);
        } catch (\Throwable $e) {
            Log::warning('Auto translation failed', [
                'from' => $from,
                'to' => $to,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function cleanupTranslation(string $source, string $translated, ?string $context): string
    {
        if ($context) {
            $prefixes = [
                "[{$context}]",
                "{$context}:",
                "{$context} -",
            ];

            foreach ($prefixes as $prefix) {
                if (str_starts_with($translated, $prefix)) {
                    $translated = trim(mb_substr($translated, mb_strlen($prefix)));
                }
            }
        }

        if (mb_strtoupper($translated) === mb_strtoupper($source)) {
            return $translated;
        }

        return $translated;
    }

    protected function httpClient(): PendingRequest
    {
        $client = Http::timeout(20);

        if (! $this->shouldVerifySsl()) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    protected function shouldVerifySsl(): bool
    {
        $configured = config('shop.auto_translate.verify_ssl');

        if ($configured !== null) {
            return filter_var($configured, FILTER_VALIDATE_BOOL);
        }

        return app()->environment('production');
    }
}
