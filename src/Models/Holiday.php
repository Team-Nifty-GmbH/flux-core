<?php

namespace FluxErp\Models;

use FluxErp\Enums\DayPartEnum;
use FluxErp\Models\Pivots\HolidayLocation;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Holiday extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Holiday $model): void {
            if ($model->isDirty('day_part_enum')) {
                $model->is_half_day = $model->day_part_enum !== DayPartEnum::FullDay;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'day_part_enum' => DayPartEnum::class,
            'is_active' => 'boolean',
            'is_half_day' => 'boolean',
            'is_recurring' => 'boolean',
        ];
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'holiday_location')
            ->using(HolidayLocation::class);
    }
}
