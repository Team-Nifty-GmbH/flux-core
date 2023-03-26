<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        app()->setlocale(
            $request->header('content-language') ??
            Auth::user()?->language->language_code ??
            config('app.locale')
        );

        return $next($request);
    }
}
