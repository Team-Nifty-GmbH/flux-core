<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeSocketId
{
    public function handle(Request $request, Closure $next): mixed
    {
        $socketId = $request->headers->get('X-Socket-ID');

        // Echo sends the literal string "undefined" until it has connected, which later crashes
        // the broadcast queue in Pusher::validate_socket_id(). Same format check as Pusher itself.
        if (! is_null($socketId) && preg_match('/\A\d+\.\d+\z/', $socketId) !== 1) {
            $request->headers->remove('X-Socket-ID');
        }

        return $next($request);
    }
}
