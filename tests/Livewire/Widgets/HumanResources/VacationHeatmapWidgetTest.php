<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\VacationHeatmapWidget;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
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
    Livewire::test(VacationHeatmapWidget::class)
        ->assertOk();
});

test('heatmap shows weeks with absence data', function (): void {
    app(AbsenceRequest::class)->create([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Approved,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(5),
        'days_requested' => 3,
        'work_days_affected' => 3,
    ]);

    $component = Livewire::test(VacationHeatmapWidget::class)
        ->assertOk();

    $weeks = $component->get('weeks');

    expect($weeks)->not->toBeEmpty();

    // Find at least one day with absent_count > 0
    $hasAbsence = false;
    foreach ($weeks as $week) {
        foreach ($week as $day) {
            if ($day !== null && ($day['absent_count'] ?? 0) > 0) {
                $hasAbsence = true;

                break 2;
            }
        }
    }

    expect($hasAbsence)->toBeTrue();
});

test('heatmap is empty when no absences exist', function (): void {
    $component = Livewire::test(VacationHeatmapWidget::class)
        ->assertOk();

    $weeks = $component->get('weeks');

    expect($weeks)->not->toBeEmpty();

    // All days should have absent_count of 0
    foreach ($weeks as $week) {
        foreach ($week as $day) {
            if ($day !== null) {
                expect($day['absent_count'])->toBe(0);
            }
        }
    }
});
