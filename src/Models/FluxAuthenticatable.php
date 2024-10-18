<?php

namespace FluxErp\Models;

use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Foundation\Auth\User;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;

abstract class FluxAuthenticatable extends User
{
    use CausesActivity, HasApiTokens, ResolvesRelationsThroughContainer;
}
