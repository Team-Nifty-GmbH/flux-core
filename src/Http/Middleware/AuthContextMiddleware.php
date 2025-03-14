<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Throwable;

class AuthContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            Context::add('user', Auth::user()->getMorphClass() . ':' . Auth::id());
        } catch (Throwable) {
            Context::forget('user');
        }

        return $next($request);
    }
}
