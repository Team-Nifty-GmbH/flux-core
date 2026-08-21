<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $url = $request->getPathInfo();
        $properties = [
            'full_url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        defer(fn () => activity()
            ->causedBy($user)
            ->withProperties($properties)
            ->event('visit')
            ->log($url)
        );

        return $next($request);
    }
}
