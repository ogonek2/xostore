<?php

namespace App\Support\Seo;

final class PageSeo
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $canonical = null,
        public bool $robotsIndex = true,
        public bool $robotsFollow = true,
        public ?string $ogImage = null,
        public string $ogType = 'website',
    ) {}

    public function documentTitle(): string
    {
        $shop = (string) config('shop.name');

        if (str_contains($this->title, $shop)) {
            return $this->title;
        }

        return "{$this->title} — {$shop}";
    }

    public function robots(): string
    {
        if (! $this->robotsIndex) {
            return 'noindex, nofollow';
        }

        return $this->robotsFollow ? 'index, follow' : 'index, nofollow';
    }

    public function canonicalUrl(): string
    {
        return $this->canonical ?? url()->current();
    }
}
