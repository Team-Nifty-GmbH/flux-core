<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Settings\SecuritySettings;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $forced = $user
            && method_exists($user, 'hasTwoFactorMethodConfigured')
            && ($user->force_two_factor || app(SecuritySettings::class)->force_two_factor)
            && ! $user->hasTwoFactorMethodConfigured();

        if (
            $forced
            && ! $request->routeIs('two-factor.setup')
            && ! $request->routeIs('logout')
        ) {
            return redirect()->route('two-factor.setup');
        }

        return $next($request);
    }
}
