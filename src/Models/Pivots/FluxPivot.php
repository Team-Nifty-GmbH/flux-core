<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class FluxPivot extends Pivot
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;
}
