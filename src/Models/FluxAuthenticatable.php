<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\HasModelPermission;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Foundation\Auth\User;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;

abstract class FluxAuthenticatable extends User
{
    use BroadcastsEvents, CausesActivity, HasApiTokens, HasModelPermission, ResolvesRelationsThroughContainer;
}
