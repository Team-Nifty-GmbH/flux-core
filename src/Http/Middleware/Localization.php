<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Throwable;

class Localization
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! app()->runningUnitTests()) {
            try {
                $userLanguage = Auth::user()?->language?->language_code;
            } catch (Throwable) {
                $userLanguage = null;
            }

            app()->setlocale(
                $request->header('content-language') ??
                $userLanguage ??
                resolve_static(Language::class, 'default')?->language_code ??
                config('app.locale')
            );

            Number::useLocale(app()->getLocale());
        }

        return $next($request);
    }
}
