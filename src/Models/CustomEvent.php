<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @deprecated
 */
class CustomEvent extends Model
{
    use HasPackageFactory, LogsActivity;

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
