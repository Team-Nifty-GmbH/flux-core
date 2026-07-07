<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Models\Media;
use FluxErp\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedUserMediaOrSignature
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Guests (and embeds) keep the signed-URL contract.
        if ($request->hasValidSignature()) {
            return $next($request);
        }

        // Internal users already have full access to private media. Portal logins
        // (Address) and any other authenticatable still require a valid signature.
        if (is_a(Auth::user(), resolve_static(User::class, 'class'))) {
            $media = $request->route('media');

            // Route model binding resolves through the container and has therefore
            // already applied any global scopes - no extra query needed.
            if (is_a($media, resolve_static(Media::class, 'class'))) {
                return $next($request);
            }

            // The exists query honours any global scope a customer registers on Media
            // to restrict visibility. In core default there is no such scope, so any
            // user passes - matching the current behaviour for internal users.
            abort_unless(
                resolve_static(Media::class, 'query')->whereKey($media)->exists(),
                404,
            );

            return $next($request);
        }

        abort(403);
    }
}
