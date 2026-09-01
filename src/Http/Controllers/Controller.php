<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static bool $hasPermission = true;

    public function __construct(?string $permission = null)
    {
        if (! static::$hasPermission) {
            return;
        }

        if (! $permission && $permission = route_to_permission()) {
            $this->middleware(['permission:' . $permission]);
        }
    }

    public static function hasPermission(): bool
    {
        return static::$hasPermission;
    }
}
