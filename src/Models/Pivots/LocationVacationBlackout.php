<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Location;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationVacationBlackout extends FluxPivot
{
    protected $table = 'location_vacation_blackout';

    // Relations
    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<VacationBlackout, $this>
     */
    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
