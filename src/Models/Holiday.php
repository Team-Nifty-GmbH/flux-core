<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\HolidayLocation;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Holiday extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'month' => 'integer',
            'day' => 'integer',
            'is_recurring' => 'boolean',
            'effective_from' => 'integer',
            'effective_until' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'holiday_location')
            ->using(HolidayLocation::class);
    }
}
