<?php

namespace FluxErp\Traits;

use FluxErp\Models\Communication;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Communicatable
{
    public function communications(): MorphToMany
    {
        return $this->morphToMany(Communication::class, 'communicatable', 'communicatable');
    }
}
