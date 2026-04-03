<?php

use FluxErp\Livewire\HumanResources\AttendanceOverviewRow;
use FluxErp\Models\Employee;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(AttendanceOverviewRow::class, [
        'lazy' => false,
        'employeeId' => $employee->getKey(),
        'year' => now()->year,
        'month' => now()->month,
        'calendarDays' => [],
        'absenceTypes' => [],
        'departmentId' => 0,
    ])
        ->assertOk();
});
