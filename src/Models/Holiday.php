<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Enums\DayPartEnum;
use FluxErp\Models\Pivots\HolidayLocation;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Holiday extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Holiday $model): void {
            if ($model->isDirty('day_part')) {
                $model->is_half_day = $model->day_part?->value !== DayPartEnum::FullDay;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'day_part' => DayPartEnum::class,
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

    public function scopeIsHoliday(Builder $query, Carbon $date, ?int $locationId = null): void
    {
        $query
            ->where(fn (Builder $query) => $query
                ->whereValueBetween($date->year, ['effective_from', 'effective_until'])
                ->orWhere(fn (Builder $query) => $query
                    ->whereNull('effective_from')
                    ->where('effective_until', '>=', $date->year)
                )
                ->orWhere(fn (Builder $query) => $query
                    ->whereNull('effective_until')
                    ->where('effective_from', '<=', $date->year)
                )
                ->orWhere(fn (Builder $query) => $query
                    ->whereNull('effective_from')
                    ->whereNull('effective_until')
                )
            )
            ->where(fn (Builder $query) => $query
                ->where('month', $date->month)
                ->where('day', $date->day)
                ->orWhere('date', $date)
            )
            ->when(
                $locationId,
                fn (Builder $query) => $query
                    ->where(fn (Builder $query) => $query
                        ->whereDoesntHave('locations')
                        ->orWhereRelation('locations', 'id', $locationId)
                    ),
                fn (Builder $query) => $query
                    ->whereDoesntHave('locations')
            );
    }
}
