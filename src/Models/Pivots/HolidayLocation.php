<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Holiday;
use FluxErp\Models\Location;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayLocation extends FluxPivot
{
    protected $table = 'holiday_location';

    // Relations
    /**
     * @return BelongsTo<Holiday, $this>
     */
    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
