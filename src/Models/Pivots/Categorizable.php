<?php

namespace FluxErp\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Categorizable extends MorphPivot
{
    use BroadcastsEvents;

    protected $table = 'categorizables';
}
