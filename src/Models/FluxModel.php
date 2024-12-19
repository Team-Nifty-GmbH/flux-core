<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Model;

abstract class FluxModel extends Model
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;

    protected $guarded = [
        'id',
    ];
}
