<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Models\Pivots\HolidayLocation;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function countryRegion(): BelongsTo
    {
        return $this->belongsTo(CountryRegion::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(EmployeeDepartment::class);
    }

    public function holidays(): BelongsToMany
    {
        return $this->belongsToMany(Holiday::class, 'holiday_location')
            ->using(HolidayLocation::class);
    }

    public function isHoliday(Carbon $date): bool
    {
        return $this->holidays()
            ->whereDate('date', $date->toDateString())
            ->exists();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
