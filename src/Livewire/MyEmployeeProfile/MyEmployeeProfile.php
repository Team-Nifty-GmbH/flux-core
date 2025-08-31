<?php

namespace FluxErp\Livewire\MyEmployeeProfile;

use FluxErp\Htmlables\TabButton;
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

    public function getTabs(): array
    {
        return [
            TabButton::make('employee.dashboard')
                ->text(__('Dashboard'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.general')
                ->text(__('General')),
            TabButton::make('my-employee-profile.absence-requests')
                ->text(__('Absence Requests'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.employee-balance-adjustments')
                ->text(__('Employee Balance Adjustments'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('my-employee-profile.employee-days')
                ->text(__('Employee Days'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.attachments')
                ->text(__('Attachments'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.comments')
                ->text(__('Comments'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
        ];
    }
}
