<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Location;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationBlackoutLocation extends FluxPivot
{
    public $incrementing = true;

    public $primaryKey = 'pivot_id';

    public $timestamps = false;

    protected $table = 'vacation_blackout_location';

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
