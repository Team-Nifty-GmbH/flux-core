<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\EmployeeDepartmentVacationBlackout;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDepartment extends FluxModel
{
    use HasParentChildRelations, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'employee_department_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    /**
     * @return BelongsToMany<VacationBlackout, $this>
     */
    public function vacationBlackouts(): BelongsToMany
    {
        return $this->belongsToMany(VacationBlackout::class, 'employee_department_vacation_blackout')
            ->using(EmployeeDepartmentVacationBlackout::class);
    }

    // Public methods
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
