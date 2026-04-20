<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Calendar;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Calendarable extends MorphPivot
{
    use ResolvesRelationsThroughContainer;

    public $timestamps = false;

    protected $table = 'calendarable';

    protected $primaryKey = 'pivot_id';

    protected $guarded = ['pivot_id'];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function calendarable(): MorphTo
    {
        return $this->morphTo();
    }
}
