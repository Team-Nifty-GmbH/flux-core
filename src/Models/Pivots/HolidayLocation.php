<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Holiday;
use FluxErp\Models\Location;
use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayLocation extends FluxPivot
{
    use HasUserModification;

    public $incrementing = true;

    public $primaryKey = 'pivot_id';

    public $timestamps = false;

    protected $table = 'holiday_location';

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
