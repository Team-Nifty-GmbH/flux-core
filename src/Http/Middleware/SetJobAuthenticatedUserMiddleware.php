<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class SetJobAuthenticatedUserMiddleware
{
    public function handle($job, Closure $next)
    {
        if (! auth()->check() && $context = Context::get('user')) {
            $context = explode(':', $context);
            Auth::setUser(morphed_model($context[0])::query()->whereKey($context[1])->first());
        }

        return $next($job);
    }
}
