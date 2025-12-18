<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Holiday;
use FluxErp\Models\Location;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayLocation extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
