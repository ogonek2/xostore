<?php

namespace App\Support\Shop;

use App\Support\Media\MediaUrl;
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
            'paragraph' => static::renderParagraph($node),
            'heading' => static::renderHeading($node),
            'bulletList' => '<ul class="landing-prose__list">'.static::renderListItems($node).'</ul>',
            'orderedList' => '<ol class="landing-prose__list landing-prose__list--ordered">'.static::renderListItems($node).'</ol>',
            'taskList' => '<ul class="landing-prose__task-list">'.static::renderTaskItems($node).'</ul>',
            'blockquote' => '<blockquote class="landing-prose__quote">'.static::renderChildren($node).'</blockquote>',
            'codeBlock' => static::renderCodeBlock($node),
            'horizontalRule' => '<hr class="landing-prose__hr">',
            'hardBreak' => '<br>',
            'image' => static::renderImage($node),
            'table' => static::renderTable($node),
            'tableRow' => static::renderTableRow($node),
            'tableHeader' => static::renderTableCell($node, 'th'),
            'tableCell' => static::renderTableCell($node, 'td'),
            'doc' => static::renderChildren($node),
            default => static::renderChildren($node),
        };
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderParagraph(array $node): string
    {
        $content = static::renderChildren($node);

        if ($content === '') {
            return '<p class="landing-prose__empty"><br></p>';
        }

        $class = static::alignmentClass($node['attrs']['textAlign'] ?? null, 'landing-prose__p');

        return '<p class="'.$class.'">'.$content.'</p>';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderHeading(array $node): string
    {
        $level = (int) ($node['attrs']['level'] ?? 2);
        $level = max(1, min(6, $level));
        $text = static::renderChildren($node);

        if ($text === '') {
            return '';
        }

        $class = static::alignmentClass(
            $node['attrs']['textAlign'] ?? null,
            'landing-prose__heading landing-prose__heading--h'.$level,
        );

        return "<h{$level} class=\"{$class}\">{$text}</h{$level}>";
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

            $items[] = '<li class="landing-prose__list-item">'.static::renderChildren($child).'</li>';
        }

        return implode('', $items);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderTaskItems(array $node): string
    {
        $items = [];

        foreach ($node['content'] ?? [] as $child) {
            if (! is_array($child) || ($child['type'] ?? '') !== 'taskItem') {
                continue;
            }

            $checked = (bool) ($child['attrs']['checked'] ?? false);
            $class = $checked ? 'landing-prose__task-item is-checked' : 'landing-prose__task-item';

            $items[] = '<li class="'.$class.'">'
                .'<span class="landing-prose__task-marker" aria-hidden="true"></span>'
                .'<span class="landing-prose__task-content">'.static::renderChildren($child).'</span>'
                .'</li>';
        }

        return implode('', $items);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderCodeBlock(array $node): string
    {
        $text = '';

        foreach ($node['content'] ?? [] as $child) {
            if (is_array($child) && ($child['type'] ?? '') === 'text') {
                $text .= (string) ($child['text'] ?? '');
            }
        }

        if ($text === '') {
            return '';
        }

        return '<pre class="landing-prose__pre"><code class="landing-prose__code-block">'
            .e($text)
            .'</code></pre>';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderImage(array $node): string
    {
        $attrs = $node['attrs'] ?? [];
        $src = static::resolveImageSrc((string) ($attrs['src'] ?? ''));

        if ($src === '') {
            return '';
        }

        $alt = e((string) ($attrs['alt'] ?? ''));
        $title = filled($attrs['title'] ?? null)
            ? ' title="'.e((string) $attrs['title']).'"'
            : '';

        return '<figure class="landing-prose__figure">'
            .'<img src="'.e($src).'" alt="'.$alt.'" class="landing-prose__image" loading="lazy"'.$title.'>'
            .'</figure>';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderTable(array $node): string
    {
        $rows = static::renderChildren($node);

        if ($rows === '') {
            return '';
        }

        return '<div class="landing-prose__table-wrap"><table class="landing-prose__table"><tbody>'
            .$rows
            .'</tbody></table></div>';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderTableRow(array $node): string
    {
        $cells = static::renderChildren($node);

        return $cells !== '' ? '<tr class="landing-prose__table-row">'.$cells.'</tr>' : '';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function renderTableCell(array $node, string $tag): string
    {
        $content = static::renderChildren($node);
        $colspan = (int) ($node['attrs']['colspan'] ?? 1);
        $rowspan = (int) ($node['attrs']['rowspan'] ?? 1);
        $attrs = '';

        if ($colspan > 1) {
            $attrs .= ' colspan="'.$colspan.'"';
        }

        if ($rowspan > 1) {
            $attrs .= ' rowspan="'.$rowspan.'"';
        }

        return '<'.$tag.' class="landing-prose__table-cell"'.$attrs.'>'.$content.'</'.$tag.'>';
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
    protected static function renderTextNode(array $node): string
    {
        $text = e((string) ($node['text'] ?? ''));

        foreach ($node['marks'] ?? [] as $mark) {
            if (! is_array($mark)) {
                continue;
            }

            $text = match ($mark['type'] ?? '') {
                'bold' => "<strong class=\"landing-prose__bold\">{$text}</strong>",
                'italic' => "<em class=\"landing-prose__italic\">{$text}</em>",
                'underline' => "<u class=\"landing-prose__underline\">{$text}</u>",
                'strike' => "<s class=\"landing-prose__strike\">{$text}</s>",
                'link' => static::renderLink($text, $mark['attrs'] ?? []),
                'code' => "<code class=\"landing-prose__code\">{$text}</code>",
                'highlight' => "<mark class=\"landing-prose__mark\">{$text}</mark>",
                'subscript' => "<sub class=\"landing-prose__sub\">{$text}</sub>",
                'superscript' => "<sup class=\"landing-prose__sup\">{$text}</sup>",
                default => $text,
            };
        }

        return $text;
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    protected static function renderLink(string $text, array $attrs): string
    {
        $href = e((string) ($attrs['href'] ?? '#'));
        $target = ($attrs['target'] ?? null) === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : ' rel="noopener"';

        return '<a href="'.$href.'" class="landing-prose__link"'.$target.'>'.$text.'</a>';
    }

    protected static function resolveImageSrc(string $src): string
    {
        $src = trim($src);

        if ($src === '') {
            return '';
        }

        return MediaUrl::fromPath($src) ?? $src;
    }

    protected static function alignmentClass(?string $align, string $baseClass): string
    {
        $suffix = match ($align) {
            'center' => 'is-center',
            'right', 'end' => 'is-right',
            'justify' => 'is-justify',
            default => 'is-left',
        };

        return trim($baseClass.' landing-prose__align-'.$suffix);
    }

    public static function plain(mixed $value): string
    {
        return RichTextTranslation::extractPlainText($value);
    }
}
