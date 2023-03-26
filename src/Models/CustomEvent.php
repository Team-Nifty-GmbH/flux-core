<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomEvent extends Model
{
    use HasPackageFactory, HasUserModification;

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
