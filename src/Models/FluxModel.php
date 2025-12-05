<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\HasModelPermission;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Model;

abstract class FluxModel extends Model
{
    use BroadcastsEvents, HasModelPermission, ResolvesRelationsThroughContainer;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
