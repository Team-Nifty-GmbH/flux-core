<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Localization
{
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $userLanguage = (app('migrator')->repositoryExists() ? Auth::user()?->language?->language_code : null);
        } catch (QueryException) {
            $userLanguage = null;
        }

        app()->setlocale(
            $request->header('content-language') ??
            $userLanguage ??
            config('app.locale')
        );

        return $next($request);
    }
}
