<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @deprecated
 */
class CustomEvent extends Model
{
    use HasPackageFactory, HasUserModification;

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
