<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Jobs\TrackVisitJob;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $url = $request->getPathInfo();

        TrackVisitJob::dispatch($url, [
            'full_url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $next($request);
    }
}
