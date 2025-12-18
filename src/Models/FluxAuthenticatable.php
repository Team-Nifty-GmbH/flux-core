<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\HasModelPermission;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;

abstract class FluxAuthenticatable extends User
{
    use BroadcastsEvents, CausesActivity, HasApiTokens, HasModelPermission, ResolvesRelationsThroughContainer;

    public function deviceTokens(): MorphMany
    {
        return $this->morphMany(DeviceToken::class, 'authenticatable');
    }
}
