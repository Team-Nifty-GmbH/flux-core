<?php

namespace FluxErp\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;
use Illuminate\Support\Facades\Route;

class Authenticate extends BaseAuthenticate
{
    /**
     * @throws AuthenticationException
     */
    public function handle($request, \Closure $next, ...$guards): mixed
    {
        // if a token is set in get parameters, set it as the bearer token
        if ($request->get('token')) {
            request()->headers->add(['Authorization' => 'Bearer ' . $request->token]);
            $guards = ['token'];
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * @throws AuthenticationException
     */
    protected function unauthenticated($request, array $guards): void
    {
        if ($request->isJson() || Route::current()->getPrefix() === 'api') {
            request()->headers->add(['Accept' => 'application/json']);
        }

        parent::unauthenticated($request, $guards);
    }
}
