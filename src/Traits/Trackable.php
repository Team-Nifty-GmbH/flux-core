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

    /**
     * adds the calculated costs to the column
     * override per model if applicable
     */
    public function costColumn(): ?string
    {
        return null;
    }
}
