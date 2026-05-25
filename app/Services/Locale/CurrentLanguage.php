<?php

namespace App\Services\Locale;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

class CurrentLanguage
{
    protected ?Language $language = null;

    public function set(Language $language): void
    {
        $this->language = $language;
        app()->setLocale($language->code);
    }

    public function get(): Language
    {
        if ($this->language) {
            return $this->language;
        }

        $code = session('locale', config('shop.default_language'));

        $cached = Cache::get("language.{$code}");
        if ($cached instanceof Language) {
            $this->language = $cached;
            app()->setLocale($this->language->code);

            return $this->language;
        }

        $language = Language::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first()
            ?? Language::default();

        if (! $language) {
            throw new \RuntimeException(
                'No active language configured. Run: php artisan db:seed --class=LanguageSeeder'
            );
        }

        Cache::put("language.{$code}", $language, now()->addDay());

        $this->language = $language;
        app()->setLocale($language->code);

        return $this->language;
    }

    public function code(): string
    {
        return $this->get()->code;
    }
}
