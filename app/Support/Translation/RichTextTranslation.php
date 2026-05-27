<?php

namespace App\Support\Translation;

final class RichTextTranslation
{
    public static function extractPlainText(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return static::extractFromTipTap($decoded);
            }

            return trim(strip_tags($value));
        }

        if (is_array($value)) {
            return static::extractFromTipTap($value);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $document
     */
    public static function extractFromTipTap(array $document): string
    {
        $parts = [];

        foreach ($document['content'] ?? [] as $block) {
            $text = static::extractFromNode($block);

            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return trim(implode("\n\n", $parts));
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function extractFromNode(array $node): string
    {
        if (($node['type'] ?? '') === 'text') {
            return (string) ($node['text'] ?? '');
        }

        $parts = [];

        foreach ($node['content'] ?? [] as $child) {
            if (! is_array($child)) {
                continue;
            }

            $text = static::extractFromNode($child);

            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return implode('', $parts);
    }

    public static function fromPlainText(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            return json_encode(static::emptyDocument(), JSON_UNESCAPED_UNICODE);
        }

        $paragraphs = preg_split("/\r\n|\r|\n/", $text) ?: [$text];
        $content = [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            $content[] = [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'start'],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $paragraph,
                    ],
                ],
            ];
        }

        if ($content === []) {
            return json_encode(static::emptyDocument(), JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'type' => 'doc',
            'content' => $content,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function emptyDocument(): array
    {
        return [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [],
                ],
            ],
        ];
    }
}
