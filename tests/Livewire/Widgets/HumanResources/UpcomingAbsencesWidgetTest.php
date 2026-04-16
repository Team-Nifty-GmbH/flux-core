<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\UpcomingAbsencesWidget;
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
    Livewire::test(UpcomingAbsencesWidget::class)
        ->assertOk();
});

test('shows upcoming approved absence within 14 days', function (): void {
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

    $component = Livewire::test(UpcomingAbsencesWidget::class)
        ->assertOk();

    $absences = $component->get('absences');

    expect($absences)->toHaveCount(1)
        ->and($absences[0]['employee_name'])->toContain('Employee')
        ->and($absences[0]['absence_type'])->toBe('Urlaub')
        ->and((float) $absences[0]['days'])->toBe(3.0);
});

test('excludes absences beyond 14 days', function (): void {
    app(AbsenceRequest::class)->create([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Approved,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(20),
        'end_date' => now()->addDays(22),
        'days_requested' => 3,
        'work_days_affected' => 3,
    ]);

    $component = Livewire::test(UpcomingAbsencesWidget::class)
        ->assertOk();

    expect($component->get('absences'))->toBeEmpty();
});
