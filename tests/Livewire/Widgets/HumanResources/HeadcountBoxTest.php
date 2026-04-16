<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\HeadcountBox;
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
    Livewire::test(HeadcountBox::class)
        ->assertOk();
});

test('counts only employed employees', function (): void {
    $employees = collect();

    for ($i = 0; $i < 3; $i++) {
        $employee = app(Employee::class)->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'user_id' => $this->user->getKey(),
            'firstname' => 'Active',
            'lastname' => 'Employee' . $i,
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

    $terminated = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Terminated',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYears(2),
        'termination_date' => now()->subMonth(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $terminated->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYears(2),
        'valid_until' => now()->subMonth(),
    ]);

    $component = Livewire::test(HeadcountBox::class)
        ->assertOk();

    expect($component->get('sum'))->toBe(Number::format(3));
});

test('sub value shows hires and departures for current month', function (): void {
    $newHire = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'New',
        'lastname' => 'Hire',
        'is_active' => true,
        'employment_date' => now()->startOfMonth()->addDays(2),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $newHire->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->startOfMonth()->addDays(2),
        'valid_until' => null,
    ]);

    $departure = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Leaving',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear(),
        'termination_date' => now()->startOfMonth()->addDays(5),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $departure->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => now()->startOfMonth()->addDays(5),
    ]);

    $component = Livewire::test(HeadcountBox::class)
        ->assertOk();

    $subValue = $component->get('subValue');
    expect($subValue)->toContain('+1')
        ->and($subValue)->toContain('-1');
});
