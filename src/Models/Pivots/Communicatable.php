<?php

namespace FluxErp\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Communicatable extends MorphPivot
{
    protected $table = 'communicatable';

    protected $guarded = [
        'id',
    ];

    public $timestamps = false;
}
