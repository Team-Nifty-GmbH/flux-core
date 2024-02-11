<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Portal
{
    public function handle(Request $request, Closure $next): mixed
    {
        config(['livewire.layout' => 'flux::components.layouts.portal']);

        return $next($request);
    }
}
