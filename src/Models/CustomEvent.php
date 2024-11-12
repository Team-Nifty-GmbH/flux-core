<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @deprecated
 */
class CustomEvent extends FluxModel
{
    use HasPackageFactory, LogsActivity;

    protected $guarded = ['id'];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
