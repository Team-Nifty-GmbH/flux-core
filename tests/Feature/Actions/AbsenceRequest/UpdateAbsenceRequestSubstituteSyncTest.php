<?php

use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\UpdateAbsenceRequest;
use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;

beforeEach(function (): void {
    $this->wtm = CreateWorkTimeModel::make([
        'name' => 'Standard',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
        'is_active' => true,
    ])->validate()->execute();

    $this->absenceType = CreateAbsenceType::make([
        'name' => 'Other Leave',
        'code' => 'OTH',
        'color' => '#FF0000',
        'percentage_deduction' => 1.0,
        'employee_can_create' => 'yes',
        'affects_vacation' => false,
        'affects_overtime' => false,
        'affects_sick_leave' => false,
    ])->validate()->execute();

    $this->employee = CreateEmployee::make([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();

    $this->substituteA = CreateEmployee::make([
        'firstname' => 'Alice',
        'lastname' => 'Smith',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();

    $this->substituteB = CreateEmployee::make([
        'firstname' => 'Bob',
        'lastname' => 'Jones',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();

    $this->absenceRequest = CreateAbsenceRequest::make([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->checkPermission()->validate()->execute();

    $this->absenceRequest->substitutes()->sync([$this->substituteA->getKey()]);
});

test('update syncs the substitutes relation', function (): void {
    UpdateAbsenceRequest::make([
        'id' => $this->absenceRequest->getKey(),
        'substitutes' => [$this->substituteB->getKey()],
    ])->checkPermission()->validate()->execute();

    expect($this->absenceRequest->substitutes()->pluck('employees.id')->all())
        ->toBe([$this->substituteB->getKey()]);
});

test('update without a substitutes key leaves the relation untouched', function (): void {
    UpdateAbsenceRequest::make([
        'id' => $this->absenceRequest->getKey(),
        'reason' => 'unrelated update',
    ])->checkPermission()->validate()->execute();

    expect($this->absenceRequest->substitutes()->pluck('employees.id')->all())
        ->toBe([$this->substituteA->getKey()]);
});
