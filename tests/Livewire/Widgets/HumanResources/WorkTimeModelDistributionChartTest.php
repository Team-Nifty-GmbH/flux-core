<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\WorkTimeModelDistributionChart;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->fullTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Vollzeit 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $this->partTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Teilzeit 20h',
        'weekly_hours' => 20,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(WorkTimeModelDistributionChart::class)
        ->assertOk();
});

test('shows distribution of work time models', function (): void {
    // 2 employees on full time
    for ($i = 0; $i < 2; $i++) {
        $employee = app(Employee::class)->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'user_id' => $this->user->getKey(),
            'firstname' => 'FullTime',
            'lastname' => 'Employee' . $i,
            'is_active' => true,
            'employment_date' => now()->subYear(),
        ]);

        app(EmployeeWorkTimeModel::class)->create([
            'employee_id' => $employee->getKey(),
            'work_time_model_id' => $this->fullTimeModel->getKey(),
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);
    }

    // 1 employee on part time
    $partTimeEmployee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'PartTime',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $partTimeEmployee->getKey(),
        'work_time_model_id' => $this->partTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    $component = Livewire::test(WorkTimeModelDistributionChart::class)
        ->assertOk();

    $labels = $component->get('labels');
    $series = $component->get('series');

    expect($labels)->toContain('Vollzeit 40h')
        ->and($labels)->toContain('Teilzeit 20h')
        ->and($series)->toHaveCount(2);
});

test('empty when no employed employees exist', function (): void {
    $component = Livewire::test(WorkTimeModelDistributionChart::class)
        ->assertOk();

    expect($component->get('series'))->toBeEmpty();
});
