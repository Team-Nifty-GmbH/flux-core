<?php

namespace FluxErp\Traits;

use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Trackable
{
    /**
     * adds the calculated costs to the column
     * override per model if applicable
     */
    public function costColumn(): ?string
    {
        return null;
    }

    public function getContactId(): ?int
    {
        return null;
    }

    public function workTimes(): MorphMany
    {
        return $this->morphMany(WorkTime::class, 'trackable');
    }
}
