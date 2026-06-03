<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\AbsenceRequest\AbsenceRequest;
use FluxErp\Models\AbsenceRequest as AbsenceRequestModel;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
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

    $absenceRequest = app(AbsenceRequestModel::class)->create([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'state' => 'pending',
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek(),
    ]);

    Livewire::test(AbsenceRequest::class, ['id' => $absenceRequest->getKey()])
        ->assertOk();
});

test('mount fills form with absence request data', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $absenceType = app(AbsenceType::class)->create([
        'name' => 'Urlaub',
        'code' => 'URL',
        'color' => '#22c55e',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => false,
        'affects_sick_leave' => false,
        'affects_vacation' => true,
        'is_active' => true,
    ]);

    $startDate = now()->addWeek()->format('Y-m-d');

    $absenceRequest = app(AbsenceRequestModel::class)->create([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'state' => 'pending',
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => $startDate,
        'end_date' => $startDate,
        'reason' => 'Vacation test',
    ]);

    Livewire::test(AbsenceRequest::class, ['id' => $absenceRequest->getKey()])
        ->assertOk()
        ->assertSet('absenceRequestForm.id', $absenceRequest->getKey())
        ->assertSet('absenceRequestForm.employee_id', $employee->getKey())
        ->assertSet('absenceRequestForm.absence_type_id', $absenceType->getKey())
        ->assertSet('absenceRequestForm.reason', 'Vacation test');
});

test('can update absence request via save', function (): void {
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

    $absenceRequest = app(AbsenceRequestModel::class)->create([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'state' => 'pending',
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek(),
    ]);

    Livewire::test(AbsenceRequest::class, ['id' => $absenceRequest->getKey()])
        ->set('absenceRequestForm.reason', 'Updated reason')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($absenceRequest->refresh()->reason)->toEqual('Updated reason');
});

test('can approve absence request', function (): void {
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

    $absenceRequest = app(AbsenceRequestModel::class)->create([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'state' => 'pending',
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->next('Monday'),
        'end_date' => now()->next('Monday'),
    ]);

    Livewire::test(AbsenceRequest::class, ['id' => $absenceRequest->getKey()])
        ->call('approve')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($absenceRequest->refresh()->state->value)->toEqual('approved');
});

test('can reject absence request', function (): void {
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

    $absenceRequest = app(AbsenceRequestModel::class)->create([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'state' => 'pending',
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek(),
    ]);

    Livewire::test(AbsenceRequest::class, ['id' => $absenceRequest->getKey()])
        ->call('reject')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($absenceRequest->refresh()->state->value)->toEqual('rejected');
});
