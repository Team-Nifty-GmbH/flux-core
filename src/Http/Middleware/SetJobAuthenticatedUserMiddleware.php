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
        $previous = auth()->user();
        $user = morph_to(Context::get('user'));

        if ($user instanceof Authenticatable) {
            if (! $previous || $previous->isNot($user)) {
                Auth::setUser($user);
            }
        } elseif ($previous) {
            // Queue workers are long-running processes. Without a reset, a job
            // dispatched without a user context (e.g. by the scheduler) would
            // run as whatever user the previous job authenticated.
            auth()->forgetUser();
        }

        try {
            return $next($job);
        } finally {
            // Restore the dispatcher state: queue workers stay clean between
            // jobs, sync dispatches don't leak the job user into the
            // surrounding request or test.
            if ($previous) {
                Auth::setUser($previous);
            } elseif (auth()->check()) {
                auth()->forgetUser();
            }
        }
    }
}
