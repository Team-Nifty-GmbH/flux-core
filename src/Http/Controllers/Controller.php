<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(?string $permission = null)
    {
        if (! $permission && $permission = route_to_permission()) {
            $this->middleware(['permission:'.$permission]);
        }
    }
}
