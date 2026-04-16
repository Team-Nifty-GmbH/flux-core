<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\DepartmentHeadcountChart;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
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
    Livewire::test(DepartmentHeadcountChart::class)
        ->assertOk();
});

test('shows department headcount data', function (): void {
    $engineering = app(EmployeeDepartment::class)->create([
        'name' => 'Engineering',
        'is_active' => true,
    ]);

    $sales = app(EmployeeDepartment::class)->create([
        'name' => 'Sales',
        'is_active' => true,
    ]);

    // 2 employees in Engineering
    for ($i = 0; $i < 2; $i++) {
        $employee = app(Employee::class)->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'user_id' => $this->user->getKey(),
            'firstname' => 'Eng',
            'lastname' => 'Employee' . $i,
            'is_active' => true,
            'employment_date' => now()->subYear(),
            'employee_department_id' => $engineering->getKey(),
        ]);

        app(EmployeeWorkTimeModel::class)->create([
            'employee_id' => $employee->getKey(),
            'work_time_model_id' => $this->workTimeModel->getKey(),
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);
    }

    // 1 employee in Sales
    $salesEmployee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Sales',
        'lastname' => 'Person',
        'is_active' => true,
        'employment_date' => now()->subYear(),
        'employee_department_id' => $sales->getKey(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $salesEmployee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    $component = Livewire::test(DepartmentHeadcountChart::class)
        ->assertOk();

    $series = $component->get('series');
    $xaxis = $component->get('xaxis');

    expect($xaxis['categories'])->toContain('Engineering')
        ->and($xaxis['categories'])->toContain('Sales')
        ->and($series)->toHaveCount(1)
        ->and($series[0]['data'])->toContain(2)
        ->and($series[0]['data'])->toContain(1);
});

test('empty series when no departments have employees', function (): void {
    $component = Livewire::test(DepartmentHeadcountChart::class)
        ->assertOk();

    expect($component->get('series'))->toBeEmpty();
});
