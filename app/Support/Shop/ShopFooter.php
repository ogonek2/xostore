<?php

namespace App\Support\Shop;

use App\Enums\SocialNetwork;
use App\Models\FooterSettings;
use Illuminate\Support\Facades\Schema;

final class ShopFooter
{
    /**
     * @return array<string, mixed>
     */
    public static function data(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $settings = Schema::hasTable('footer_settings')
            ? FooterSettings::instance()
            : null;

        return [
            'newsletter' => static::newsletter($settings, $locale),
            'brand' => static::brand($settings, $locale),
            'social' => static::social($settings, $locale),
            'columns' => static::columns($locale),
            'contact' => static::contact($settings, $locale),
            'payments' => static::payments($settings, $locale),
            'bottom' => static::bottom($settings, $locale),
            'current_year' => now()->year,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function newsletter(?FooterSettings $settings, string $locale): ?array
    {
        if (! $settings?->newsletter_enabled) {
            return null;
        }

        $fields = ['eyebrow', 'title', 'hint', 'placeholder', 'submit', 'success', 'error'];
        $texts = [];

        foreach ($fields as $field) {
            $texts[$field] = $settings->text('newsletter', $field, $locale);
        }

        if (collect($texts)->filter()->isEmpty()) {
            return null;
        }

        return [
            'endpoint' => route('api.newsletter.subscribe', ['locale' => $locale]),
            ...$texts,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function brand(?FooterSettings $settings, string $locale): ?array
    {
        $tagline = $settings?->text('brand', 'tagline', $locale);

        return [
            'shop_name' => config('shop.name'),
            'home_url' => route('home', ['locale' => $locale]),
            'tagline' => $tagline,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function social(?FooterSettings $settings, string $locale): ?array
    {
        if (! $settings?->social_enabled) {
            return null;
        }

        $title = $settings->text('social', 'title', $locale);
        $links = collect($settings->social['links'] ?? [])
            ->filter(fn ($link) => ! empty($link['url']) && ($link['is_active'] ?? true))
            ->map(function (array $link) use ($locale) {
                $labels = $link['labels'] ?? [];
                $default = (string) config('shop.default_language', 'pl');
                $network = SocialNetwork::tryFrom((string) ($link['network'] ?? 'link'));
                $label = $labels[$locale]
                    ?? $labels[$default]
                    ?? $network?->label()
                    ?? 'Social';

                return [
                    'network' => $network?->value ?? (string) ($link['network'] ?? 'link'),
                    'label' => (string) $label,
                    'url' => (string) $link['url'],
                ];
            })
            ->values()
            ->all();

        if ($title === null && $links === []) {
            return null;
        }

        return [
            'title' => $title,
            'links' => $links,
        ];
    }

    /**
     * @return list<array{title: string, links: list<array{label: string, url: string, open_in_new_tab: bool}>}>
     */
    protected static function columns(string $locale): array
    {
        $columns = [];

        foreach (ShopNavigation::tree('footer') as $item) {
            if (($item['type'] ?? '') !== 'simple') {
                continue;
            }

            $links = collect($item['children'] ?? [])
                ->filter(fn ($child) => ! empty($child['label']) && ! empty($child['url']))
                ->map(fn ($child) => [
                    'label' => $child['label'],
                    'url' => $child['url'],
                    'open_in_new_tab' => (bool) ($child['open_in_new_tab'] ?? false),
                ])
                ->values()
                ->all();

            if (empty($item['label']) && $links === []) {
                continue;
            }

            $columns[] = [
                'title' => (string) ($item['label'] ?? ''),
                'links' => $links,
            ];
        }

        return $columns;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function contact(?FooterSettings $settings, string $locale): ?array
    {
        if (! $settings?->contact_enabled) {
            return null;
        }

        $email = config('shop.contact.email');
        $phone = config('shop.contact.phone');

        if (! $email && ! $phone) {
            return null;
        }

        return [
            'title' => $settings->text('contact', 'title', $locale),
            'email_label' => $settings->text('contact', 'email_label', $locale),
            'phone_label' => $settings->text('contact', 'phone_label', $locale),
            'email' => $email,
            'phone' => $phone,
            'phone_href' => $phone ? preg_replace('/\s+/', '', $phone) : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function payments(?FooterSettings $settings, string $locale): ?array
    {
        if (! $settings?->payments_enabled) {
            return null;
        }

        $methods = collect($settings->payments['methods'] ?? [])
            ->map(fn ($row) => is_array($row) ? trim((string) ($row['label'] ?? '')) : trim((string) $row))
            ->filter()
            ->values()
            ->all();

        if ($methods === []) {
            return null;
        }

        return [
            'title' => $settings->text('payments', 'title', $locale),
            'methods' => $methods,
        ];
    }

    /**
     * @return array{copyright: ?string, links: list<array{label: string, url: string, open_in_new_tab: bool}>}
     */
    protected static function bottom(?FooterSettings $settings, string $locale): array
    {
        $links = [];

        if ($settings) {
            foreach ($settings->bottom['links'] ?? [] as $link) {
                if (! ($link['is_active'] ?? true)) {
                    continue;
                }

                $labels = $link['labels'] ?? [];
                $default = (string) config('shop.default_language', 'pl');
                $label = $labels[$locale] ?? $labels[$default] ?? null;
                $url = isset($link['url']) ? ShopNavigation::resolveUrl((string) $link['url'], $locale) : null;

                if (! $label || ! $url) {
                    continue;
                }

                $links[] = [
                    'label' => $label,
                    'url' => $url,
                    'open_in_new_tab' => (bool) ($link['open_in_new_tab'] ?? false),
                ];
            }
        }

        return [
            'copyright' => $settings?->text('bottom', 'copyright', $locale),
            'links' => $links,
        ];
    }
}
