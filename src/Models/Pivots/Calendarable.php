<?php

namespace FluxErp\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Calendarable extends MorphPivot
{
    public $timestamps = false;

    protected $table = 'calendarable';

    protected $primaryKey = 'pivot_id';

    protected $guarded = ['pivot_id'];
}
