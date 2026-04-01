<?php

use FluxErp\Livewire\MyEmployeeProfile\MyEmployeeDay;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $employeeDay = app(EmployeeDay::class)->create([
        'employee_id' => $employee->getKey(),
        'date' => now(),
        'is_work_day' => true,
        'is_holiday' => false,
    ]);

    Livewire::test(MyEmployeeDay::class, ['id' => $employeeDay->getKey()])
        ->assertOk();
});
