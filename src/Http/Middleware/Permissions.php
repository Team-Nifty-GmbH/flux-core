<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Traits\HasRoles;

class Permissions
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            ! Auth::user()
            || (
                in_array(HasRoles::class, class_uses_recursive(Auth::user()))
                && Auth::user()->hasRole('Super Admin')
            )
        ) {
            return $next($request);
        }

        $permission = route_to_permission(checkPermission: false);

        if ($permission && ! Auth::user()?->checkPermissionTo($permission)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
}
