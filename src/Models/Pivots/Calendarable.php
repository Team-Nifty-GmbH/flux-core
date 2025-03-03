<?php

namespace FluxErp\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Calendarable extends MorphPivot
{
    protected $table = 'calendarables';
}
