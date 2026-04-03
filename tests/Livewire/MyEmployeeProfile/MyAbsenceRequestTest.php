<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Livewire\MyEmployeeProfile\MyAbsenceRequest;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
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

    $absenceType = app(AbsenceType::class)->create([
        'name' => 'Krank',
        'code' => 'KRK',
        'color' => '#ef4444',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => false,
        'affects_sick_leave' => true,
        'affects_vacation' => false,
        'is_active' => true,
    ]);

    $absenceRequest = app(AbsenceRequest::class)->create([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'state' => 'pending',
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek(),
    ]);

    Livewire::test(MyAbsenceRequest::class, ['id' => $absenceRequest->getKey()])
        ->assertOk();
});
