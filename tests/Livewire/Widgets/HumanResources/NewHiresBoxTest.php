<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\NewHiresBox;
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
});

test('renders successfully', function (): void {
    Livewire::test(NewHiresBox::class)
        ->assertOk();
});

test('counts new hires in current month', function (): void {
    for ($i = 0; $i < 2; $i++) {
        $employee = app(Employee::class)->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'user_id' => $this->user->getKey(),
            'firstname' => 'New',
            'lastname' => 'Hire' . $i,
            'is_active' => true,
            'employment_date' => now()->startOfMonth()->addDays($i + 1),
        ]);

        app(EmployeeWorkTimeModel::class)->create([
            'employee_id' => $employee->getKey(),
            'work_time_model_id' => $this->workTimeModel->getKey(),
            'valid_from' => now()->startOfMonth()->addDays($i + 1),
            'valid_until' => null,
        ]);
    }

    // Old employee hired last year
    $oldEmployee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Old',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $oldEmployee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    $component = Livewire::test(NewHiresBox::class)
        ->assertOk();

    expect($component->get('sum'))->toBe(Number::format(2));
});

test('sub value shows departures count', function (): void {
    $departure = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Leaving',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear(),
        'termination_date' => now()->startOfMonth()->addDays(3),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $departure->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => now()->startOfMonth()->addDays(3),
    ]);

    $component = Livewire::test(NewHiresBox::class)
        ->assertOk();

    expect($component->get('subValue'))->toContain('1');
});
