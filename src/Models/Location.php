<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\HolidayLocation;
use FluxErp\Models\Pivots\LocationVacationBlackout;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo<CountryRegion, $this>
     */
    public function countryRegion(): BelongsTo
    {
        return $this->belongsTo(CountryRegion::class);
    }

    /**
     * @return HasMany<EmployeeDepartment, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(EmployeeDepartment::class);
    }

    /**
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * @return BelongsToMany<Holiday, $this>
     */
    public function holidays(): BelongsToMany
    {
        return $this->belongsToMany(Holiday::class, 'holiday_location')
            ->using(HolidayLocation::class);
    }

    /**
     * @return BelongsToMany<VacationBlackout, $this>
     */
    public function vacationBlackouts(): BelongsToMany
    {
        return $this->belongsToMany(VacationBlackout::class, 'location_vacation_blackout')
            ->using(LocationVacationBlackout::class);
    }
}
