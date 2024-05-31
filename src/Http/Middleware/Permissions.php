<?php

namespace FluxErp\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Traits\HasRoles;

class Permissions
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
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

        try {
            $hasPermission = Auth::user()?->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist) {
            $hasPermission = true;
        }

        if ($permission && ! $hasPermission) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
}
