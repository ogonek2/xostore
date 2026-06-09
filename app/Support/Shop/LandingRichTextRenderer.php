<?php

namespace App\Support\Shop;

use App\Support\Translation\RichTextTranslation;

final class LandingRichTextRenderer
{
    public static function render(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (! is_array($decoded)) {
                return trim($value);
            }

            return static::fromTipTap($decoded);
        }

        if (is_array($value)) {
            return static::fromTipTap($value);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected static function fromTipTap(array $document): string
    {
        $html = [];

        foreach ($document['content'] ?? [] as $node) {
            if (! is_array($node)) {
                continue;
            }

            $chunk = static::renderNode($node);

            if ($chunk !== '') {
                $html[] = $chunk;
            }
        }

        return implode("\n", $html);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderNode(array $node): string
    {
        $type = $node['type'] ?? '';

        return match ($type) {
            'paragraph' => '<p>'.static::renderInline($node).'</p>',
            'heading' => static::renderHeading($node),
            'bulletList' => '<ul>'.static::renderListItems($node).'</ul>',
            'orderedList' => '<ol>'.static::renderListItems($node).'</ol>',
            'blockquote' => '<blockquote>'.static::renderInline($node).'</blockquote>',
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            default => static::renderChildren($node),
        };
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderHeading(array $node): string
    {
        $level = (int) ($node['attrs']['level'] ?? 2);
        $level = max(1, min(6, $level));
        $text = static::renderInline($node);

        return $text !== '' ? "<h{$level}>{$text}</h{$level}>" : '';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderListItems(array $node): string
    {
        $items = [];

        foreach ($node['content'] ?? [] as $child) {
            if (! is_array($child) || ($child['type'] ?? '') !== 'listItem') {
                continue;
            }

            $items[] = '<li>'.static::renderChildren($child).'</li>';
        }

        return implode('', $items);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderChildren(array $node): string
    {
        $parts = [];

        foreach ($node['content'] ?? [] as $child) {
            if (! is_array($child)) {
                continue;
            }

            if (($child['type'] ?? '') === 'text') {
                $parts[] = static::renderTextNode($child);

                continue;
            }

            $parts[] = static::renderNode($child);
        }

        return implode('', $parts);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderInline(array $node): string
    {
        return static::renderChildren($node);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderTextNode(array $node): string
    {
        $text = e((string) ($node['text'] ?? ''));

        foreach ($node['marks'] ?? [] as $mark) {
            if (! is_array($mark)) {
                continue;
            }

            $text = match ($mark['type'] ?? '') {
                'bold' => "<strong>{$text}</strong>",
                'italic' => "<em>{$text}</em>",
                'underline' => "<u>{$text}</u>",
                'strike' => "<s>{$text}</s>",
                'link' => '<a href="'.e((string) ($mark['attrs']['href'] ?? '#')).'" rel="noopener">'
                    .$text.'</a>',
                'code' => "<code>{$text}</code>",
                default => $text,
            };
        }

        return $text;
    }

    public static function plain(mixed $value): string
    {
        return RichTextTranslation::extractPlainText($value);
    }
}
