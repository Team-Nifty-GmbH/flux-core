<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class SetJobAuthenticatedUserMiddleware
{
    public function handle($job, Closure $next)
    {
        $user = morph_to(Context::get('user'));

        if ((! auth()->check() || ! auth()->user()->isNot($user)) && $user && $user instanceof Authenticatable) {
            Auth::setUser($user);
        }

        return $next($job);
    }
}
