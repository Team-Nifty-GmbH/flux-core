<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\TopOvertimeWidget;
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
});

test('renders successfully', function (): void {
    Livewire::test(TopOvertimeWidget::class)
        ->assertOk();
});

test('ranks employees by overtime descending', function (): void {
    $employeeHigh = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'High',
        'lastname' => 'Overtime',
        'is_active' => true,
        'employment_date' => now()->subYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employeeHigh->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    app(EmployeeDay::class)->create([
        'employee_id' => $employeeHigh->getKey(),
        'date' => now()->subDays(3),
        'target_hours' => 8.00,
        'actual_hours' => 13.00,
        'sick_days_used' => 0,
        'sick_hours_used' => 0,
        'vacation_days_used' => 0,
        'vacation_hours_used' => 0,
        'plus_minus_overtime_hours' => 5.00,
        'plus_minus_absence_hours' => 0,
        'is_work_day' => true,
        'is_holiday' => false,
        'break_minutes' => 30,
    ]);

    $employeeLow = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Low',
        'lastname' => 'Overtime',
        'is_active' => true,
        'employment_date' => now()->subYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employeeLow->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    app(EmployeeDay::class)->create([
        'employee_id' => $employeeLow->getKey(),
        'date' => now()->subDays(3),
        'target_hours' => 8.00,
        'actual_hours' => 9.00,
        'sick_days_used' => 0,
        'sick_hours_used' => 0,
        'vacation_days_used' => 0,
        'vacation_hours_used' => 0,
        'plus_minus_overtime_hours' => 1.00,
        'plus_minus_absence_hours' => 0,
        'is_work_day' => true,
        'is_holiday' => false,
        'break_minutes' => 30,
    ]);

    $component = Livewire::test(TopOvertimeWidget::class)
        ->assertOk();

    $employees = $component->get('employees');

    expect($employees)->toHaveCount(2)
        ->and($employees[0]['rank'])->toBe(1)
        ->and($employees[0]['name'])->toContain('High')
        ->and($employees[1]['rank'])->toBe(2)
        ->and($employees[1]['name'])->toContain('Low');
});
