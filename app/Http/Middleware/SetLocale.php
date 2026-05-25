<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Services\Locale\CurrentLanguage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        protected CurrentLanguage $currentLanguage
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->route('locale')
            ?? $request->query('lang')
            ?? session('locale')
            ?? $request->getPreferredLanguage(
                Language::query()->where('is_active', true)->pluck('code')->all()
            )
            ?? config('shop.default_language');

        $language = Language::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first()
            ?? Language::default();

        if ($language) {
            $this->currentLanguage->set($language);
            session(['locale' => $language->code]);
            app()->setLocale($language->code);
        }

        return $next($request);
    }
}
