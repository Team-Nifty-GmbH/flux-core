<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\AbsenceTypeDistributionChart;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
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

    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $this->employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(AbsenceTypeDistributionChart::class)
        ->assertOk();
});

test('shows absence type distribution with data', function (): void {
    $sickType = app(AbsenceType::class)->create([
        'name' => 'Krank',
        'code' => 'KRK',
        'color' => '#ef4444',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => false,
        'affects_sick_leave' => true,
        'affects_vacation' => false,
        'is_active' => true,
    ]);

    $dateInMonth = now()->startOfMonth()->addDays(2);
    if ($dateInMonth->isAfter(now())) {
        $dateInMonth = now()->subDay();
    }

    $employeeDay = app(EmployeeDay::class)->create([
        'employee_id' => $this->employee->getKey(),
        'date' => $dateInMonth,
        'target_hours' => 8.00,
        'actual_hours' => 0,
        'sick_days_used' => 1,
        'sick_hours_used' => 8.00,
        'vacation_days_used' => 0,
        'vacation_hours_used' => 0,
        'plus_minus_overtime_hours' => 0,
        'plus_minus_absence_hours' => 0,
        'is_work_day' => true,
        'is_holiday' => false,
        'break_minutes' => 0,
    ]);

    $absenceRequest = app(AbsenceRequest::class)->create([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $sickType->getKey(),
        'state' => AbsenceRequestStateEnum::Approved,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => $dateInMonth,
        'end_date' => $dateInMonth,
        'days_requested' => 1,
        'work_days_affected' => 1,
    ]);

    // Link absence request to employee day via pivot (only if not already linked by model events)
    if (! $absenceRequest->employeeDays()->where('employee_day_id', $employeeDay->getKey())->exists()) {
        $absenceRequest->employeeDays()->attach($employeeDay->getKey());
    }

    $component = Livewire::test(AbsenceTypeDistributionChart::class)
        ->assertOk();

    $series = $component->get('series');
    $labels = $component->get('labels');

    expect($labels)->toContain('Krank')
        ->and($series)->not->toBeEmpty();
});
