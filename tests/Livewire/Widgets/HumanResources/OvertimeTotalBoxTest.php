<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\OvertimeTotalBox;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
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
});

test('renders successfully', function (): void {
    Livewire::test(OvertimeTotalBox::class)
        ->assertOk();
});

test('calculates total overtime from employee days', function (): void {
    $employees = collect();

    for ($i = 0; $i < 2; $i++) {
        $employee = app(Employee::class)->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'user_id' => $this->user->getKey(),
            'firstname' => 'Worker',
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

    // Employee 0: 3h overtime total
    app(EmployeeDay::class)->create([
        'employee_id' => $employees[0]->getKey(),
        'date' => now()->subDays(5),
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

    app(EmployeeDay::class)->create([
        'employee_id' => $employees[0]->getKey(),
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

    // Employee 1: 1.5h overtime total
    app(EmployeeDay::class)->create([
        'employee_id' => $employees[1]->getKey(),
        'date' => now()->subDays(4),
        'target_hours' => 8.00,
        'actual_hours' => 9.50,
        'sick_days_used' => 0,
        'sick_hours_used' => 0,
        'vacation_days_used' => 0,
        'vacation_hours_used' => 0,
        'plus_minus_overtime_hours' => 1.50,
        'plus_minus_absence_hours' => 0,
        'is_work_day' => true,
        'is_holiday' => false,
        'break_minutes' => 30,
    ]);

    $component = Livewire::test(OvertimeTotalBox::class)
        ->assertOk();

    // Total: 3 + 1.5 = 4.5h
    $expectedTotal = Number::format(4.50, 2) . 'h';
    expect($component->get('sum'))->toBe($expectedTotal);

    // Average: 4.5 / 2 = 2.25h
    $expectedAverage = Number::format(2.25, 2);
    expect($component->get('subValue'))->toContain($expectedAverage);
});
