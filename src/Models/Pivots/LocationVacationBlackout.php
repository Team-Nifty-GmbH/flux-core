<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Location;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationVacationBlackout extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
