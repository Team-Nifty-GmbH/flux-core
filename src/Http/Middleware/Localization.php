<?php

namespace FluxErp\Http\Middleware;

use Carbon\Carbon as BaseCarbon;
use Closure;
use FluxErp\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Throwable;

class Localization
{
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $userLanguage = Auth::user()?->language?->language_code;
        } catch (Throwable) {
            $userLanguage = null;
        }

        $locale = $request->header('content-language') ?? $userLanguage;

        if (! $locale && $request->header('accept-language')) {
            $availableLocales = collect(Cache::memo()->rememberForever(
                'available_language_codes',
                fn () => resolve_static(Language::class, 'query')
                    ->pluck('language_code')
                    ->toArray()
            ));

            $locale = collect($request->getLanguages())
                ->flatMap(fn (string $lang) => array_filter([$lang, strstr($lang, '_', true) ?: null]))
                ->unique()
                ->first(fn (string $lang) => $availableLocales->contains($lang));
        }

        app()->setLocale(
            $locale
            ?? resolve_static(Language::class, 'default')?->language_code
            ?? config('app.locale')
        );

        Number::useLocale(app()->getLocale());
        Carbon::setLocale(app()->getLocale());
        BaseCarbon::setLocale(app()->getLocale());

        return $next($request);
    }
}
