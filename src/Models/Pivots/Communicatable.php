<?php

namespace FluxErp\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Communicatable extends MorphPivot
{
    protected $table = 'communicatable';

    protected $guarded = [
        'id',
    ];

    public $timestamps = false;
}
