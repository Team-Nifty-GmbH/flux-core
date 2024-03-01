<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoAuth
{
    public function handle(Request $request, Closure $next)
    {
        Auth::guard()->setUser(app(User::class));

        return $next($request);
    }
}
