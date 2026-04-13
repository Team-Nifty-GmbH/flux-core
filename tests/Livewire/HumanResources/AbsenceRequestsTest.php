<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\HumanResources\AbsenceRequests;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceRequests::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(AbsenceRequests::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('absenceRequestForm.id', null)
        ->assertSet('absenceRequestForm.employee_id', null)
        ->assertSet('absenceRequestForm.absence_type_id', null)
        ->assertOpensModal('absence-request-form-modal');
});

test('edit with id redirects to detail route', function (): void {
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

    $absenceRequest = app(FluxErp\Models\AbsenceRequest::class)->create([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'state' => 'pending',
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek(),
    ]);

    Livewire::test(AbsenceRequests::class)
        ->call('edit', $absenceRequest->getKey())
        ->assertRedirect();
});

test('can create absence request via save', function (): void {
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

    $startDate = now()->next('Monday')->format('Y-m-d');

    Livewire::test(AbsenceRequests::class)
        ->call('edit')
        ->set('absenceRequestForm.employee_id', $employee->getKey())
        ->set('absenceRequestForm.absence_type_id', $absenceType->getKey())
        ->set('absenceRequestForm.day_part', 'full_day')
        ->set('absenceRequestForm.start_date', $startDate)
        ->set('absenceRequestForm.end_date', $startDate)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('absence_requests', [
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'start_date' => $startDate,
    ]);
});

test('save validates required fields', function (): void {
    Livewire::test(AbsenceRequests::class)
        ->call('edit')
        ->set('absenceRequestForm.employee_id', null)
        ->set('absenceRequestForm.absence_type_id', null)
        ->call('save')
        ->assertReturned(false);
});
