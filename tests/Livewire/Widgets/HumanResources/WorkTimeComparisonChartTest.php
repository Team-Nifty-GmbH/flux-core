<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\WorkTimeComparisonChart;
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
    Livewire::test(WorkTimeComparisonChart::class)
        ->assertOk();
});

test('series contains target, actual, and overtime data', function (): void {
    $dateInMonth = now()->startOfMonth()->addDays(2);
    if ($dateInMonth->isAfter(now())) {
        $dateInMonth = now()->subDay();
    }

    app(EmployeeDay::class)->create([
        'employee_id' => $this->employee->getKey(),
        'date' => $dateInMonth,
        'target_hours' => 8.00,
        'actual_hours' => 10.00,
        'sick_days_used' => 0,
        'sick_hours_used' => 0,
        'vacation_days_used' => 0,
        'vacation_hours_used' => 0,
        'plus_minus_overtime_hours' => 2.00,
        'plus_minus_absence_hours' => 0,
        'is_work_day' => true,
        'is_holiday' => false,
        'break_minutes' => 30,
    ]);

    $component = Livewire::test(WorkTimeComparisonChart::class)
        ->assertOk();

    $series = $component->get('series');

    expect($series)->toHaveCount(3)
        ->and($series[0]['name'])->toBe(__('Target Hours'))
        ->and($series[1]['name'])->toBe(__('Actual Hours'))
        ->and($series[2]['name'])->toBe(__('Overtime'));
});
