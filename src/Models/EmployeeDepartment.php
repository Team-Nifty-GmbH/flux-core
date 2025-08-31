<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\VacationBlackoutEmployeeDepartment;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDepartment extends FluxModel
{
    use Commentable, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(EmployeeDepartment::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'employee_department_id');
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(EmployeeDepartment::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_department_id');
    }

    public function vacationBlackouts(): BelongsToMany
    {
        return $this->belongsToMany(VacationBlackout::class, 'vacation_blackout_employee_department')
            ->using(VacationBlackoutEmployeeDepartment::class);
    }
}
