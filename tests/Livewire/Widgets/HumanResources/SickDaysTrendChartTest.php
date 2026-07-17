<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\SickDaysTrendChart;
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
    Livewire::test(SickDaysTrendChart::class)
        ->assertOk();
});

test('series contains sick days data for current month', function (): void {
    $dateInMonth = now()->startOfMonth()->addDays(2);
    if ($dateInMonth->isAfter(now())) {
        $dateInMonth = now()->subDay();
    }

    app(EmployeeDay::class)->create([
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

    $component = Livewire::test(SickDaysTrendChart::class)
        ->assertOk();

    $series = $component->get('series');

    expect($series)->not->toBeEmpty()
        ->and($series[0]['name'])->toBe(__('Sick Days'))
        ->and($series[0]['data'])->toBeArray();
});
