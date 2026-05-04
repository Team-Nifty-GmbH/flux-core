<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\SickRateBox;
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
    Livewire::test(SickRateBox::class)
        ->assertOk();
});

test('calculates sick rate from employee days in current month', function (): void {
    // Travel mid-month so the four generated days fit in the [startOfMonth, now()] range.
    $this->travelTo(now()->startOfMonth()->addDays(20));

    $dateInMonth = now()->startOfMonth()->addDays(2);

    // Create 4 work days, 1 of which is sick
    for ($i = 0; $i < 4; $i++) {
        $date = $dateInMonth->copy()->addDays($i);
        if ($date->isAfter(now())) {
            break;
        }

        app(EmployeeDay::class)->create([
            'employee_id' => $this->employee->getKey(),
            'date' => $date,
            'target_hours' => 8.00,
            'actual_hours' => $i === 0 ? 0 : 8.00,
            'sick_days_used' => $i === 0 ? 1 : 0,
            'sick_hours_used' => $i === 0 ? 8.00 : 0,
            'vacation_days_used' => 0,
            'vacation_hours_used' => 0,
            'plus_minus_overtime_hours' => 0,
            'plus_minus_absence_hours' => 0,
            'is_work_day' => true,
            'is_holiday' => false,
            'break_minutes' => 30,
        ]);
    }

    $component = Livewire::test(SickRateBox::class)
        ->assertOk();

    // 1 sick day out of 4 work days = 25%
    $expectedRate = Number::format(25.0, 1) . '%';
    expect($component->get('sum'))->toBe($expectedRate);
});

test('sub value shows total sick days', function (): void {
    $this->travelTo(now()->startOfMonth()->addDays(20));

    $dateInMonth = now()->startOfMonth()->addDay();

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

    $component = Livewire::test(SickRateBox::class)
        ->assertOk();

    $expectedDays = Number::format(1.0, 1);
    expect($component->get('subValue'))->toContain($expectedDays);
});
