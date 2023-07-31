<?php

namespace FluxErp\Traits;

use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Trackable
{
    public function workTimes(): MorphMany
    {
        return $this->morphMany(WorkTime::class, 'trackable');
    }
}
