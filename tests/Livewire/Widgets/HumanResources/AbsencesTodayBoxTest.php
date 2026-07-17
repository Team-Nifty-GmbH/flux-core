<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\AbsencesTodayBox;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use Illuminate\Support\Number;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $this->absenceType = app(AbsenceType::class)->create([
        'name' => 'Urlaub',
        'code' => 'URL',
        'color' => '#22c55e',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => false,
        'affects_sick_leave' => false,
        'affects_vacation' => true,
        'is_active' => true,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(AbsencesTodayBox::class)
        ->assertOk();
});

test('counts employees absent today', function (): void {
    $employees = collect();

    for ($i = 0; $i < 2; $i++) {
        $employee = app(Employee::class)->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'user_id' => $this->user->getKey(),
            'firstname' => 'Employee',
            'lastname' => 'Nr' . $i,
            'is_active' => true,
            'employment_date' => now()->subYear(),
        ]);

        app(EmployeeWorkTimeModel::class)->create([
            'employee_id' => $employee->getKey(),
            'work_time_model_id' => $this->workTimeModel->getKey(),
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);

        $employees->push($employee);
    }

    app(AbsenceRequest::class)->create([
        'employee_id' => $employees->first()->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Approved,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'days_requested' => 3,
        'work_days_affected' => 3,
    ]);

    $component = Livewire::test(AbsencesTodayBox::class)
        ->assertOk();

    expect($component->get('sum'))->toBe(Number::format(1));
});

test('sub value shows present rate', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Present',
        'lastname' => 'Worker',
        'is_active' => true,
        'employment_date' => now()->subYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    $component = Livewire::test(AbsencesTodayBox::class)
        ->assertOk();

    expect($component->get('sum'))->toBe(Number::format(0))
        ->and($component->get('subValue'))->toContain('100');
});
