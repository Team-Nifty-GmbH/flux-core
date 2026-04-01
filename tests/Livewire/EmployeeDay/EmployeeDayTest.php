<?php

use FluxErp\Livewire\EmployeeDay\EmployeeDay;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay as EmployeeDayModel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $employeeDay = app(EmployeeDayModel::class)->create([
        'employee_id' => $employee->getKey(),
        'date' => now(),
        'is_work_day' => true,
        'is_holiday' => false,
    ]);

    Livewire::test(EmployeeDay::class, ['id' => $employeeDay->getKey()])
        ->assertOk();
});
