<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class FluxPivot extends Pivot
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $guarded = ['pivot_id'];
}
