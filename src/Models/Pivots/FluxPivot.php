<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class FluxPivot extends Pivot
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;
}
