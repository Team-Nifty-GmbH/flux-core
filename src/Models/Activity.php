<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    use BroadcastsEvents, CacheModelQueries, ResolvesRelationsThroughContainer;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
