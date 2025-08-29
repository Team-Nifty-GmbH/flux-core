<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\Employee\Employee;
use FluxErp\Models\Employee as EmployeeModel;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MyEmployeeProfile extends Employee
{
    public function mount(?int $id = null): void
    {
        $employee = resolve_static(EmployeeModel::class, 'query')
            ->whereKey(auth()->user()?->employee?->getKey())
            ->with([
                'media' => fn (MorphMany $query) => $query->where('collection_name', 'avatar'),
                'workTimeModelHistory' => function ($query): void {
                    $query->whereNull('valid_until')
                        ->with('workTimeModel');
                },
                'employeeDepartment',
                'location',
                'supervisor',
            ])
            ->firstOrFail();

        $this->employee->fill($employee);
        $this->avatar = $employee->getAvatarUrl();
    }
}
