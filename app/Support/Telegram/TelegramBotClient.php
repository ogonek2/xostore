<?php

namespace App\Support\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TelegramBotClient
{
    /**
     * @param  array<string, mixed>|null  $replyMarkup
     */
    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        $token = (string) config('shop.telegram.bot_token');

        if ($token === '') {
            return false;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        $result = $this->post($token, $payload);

        if ($result || $replyMarkup === null) {
            return $result;
        }

        Log::warning('Telegram sendMessage with button failed, retrying without button');

        unset($payload['reply_markup']);

        return $this->post($token, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function post(string $token, array $payload): bool
    {
        try {
            $response = Http::timeout(15)
                ->withOptions(['verify' => $this->shouldVerifySsl()])
                ->asJson()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Telegram sendMessage failed', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Telegram sendMessage exception', [
                'message' => $exception->getMessage(),
            ]);
        }

        return false;
    }

    protected function shouldVerifySsl(): bool
    {
        $configured = config('shop.telegram.verify_ssl');

        if ($configured !== null) {
            return filter_var($configured, FILTER_VALIDATE_BOOLEAN);
        }

        return ! app()->environment('local');
    }

    /**
     * @return array<string, mixed>
     */
    public function inlineUrlButton(string $label, string $url): array
    {
        return [
            'inline_keyboard' => [
                [
                    [
                        'text' => $label,
                        'url' => $url,
                    ],
                ],
            ],
        ];
    }
}
