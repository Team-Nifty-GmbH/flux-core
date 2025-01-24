<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\HasModelPermission;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Model;

abstract class FluxModel extends Model
{
    use BroadcastsEvents, HasModelPermission, ResolvesRelationsThroughContainer;

    protected $guarded = [
        'id',
    ];
}
