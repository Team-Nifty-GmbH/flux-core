<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\Employee\CurrentWorkTimeModel;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employee->getKey(),
        'work_time_model_id' => $workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'annual_vacation_days' => 24,
    ]);

    Livewire::test(CurrentWorkTimeModel::class, ['employeeId' => $employee->getKey()])
        ->assertOk();
});
