<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Communication;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Communicatable
{
    /**
     * @return MorphToMany<Communication, $this>
     */
    public function communications(): MorphToMany
    {
        return $this->morphToMany(Communication::class, 'communicatable', 'communicatable');
    }
}
