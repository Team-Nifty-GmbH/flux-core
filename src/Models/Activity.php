<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    use CacheModelQueries;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
