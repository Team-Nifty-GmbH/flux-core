<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Traits\BroadcastsEvents;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Categorizable extends MorphPivot
{
    use BroadcastsEvents;

    protected $table = 'categorizables';
}
