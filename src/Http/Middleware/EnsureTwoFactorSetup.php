<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->force_two_factor
            && method_exists($user, 'hasTwoFactorEnabled')
            && ! $user->hasTwoFactorEnabled()
            && ! $request->routeIs('my-profile')
            && ! $request->routeIs('logout')
        ) {
            return redirect()->route('my-profile');
        }

        return $next($request);
    }
}
