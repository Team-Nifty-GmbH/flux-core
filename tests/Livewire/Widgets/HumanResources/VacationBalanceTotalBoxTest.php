<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\VacationBalanceTotalBox;
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
});

test('renders successfully', function (): void {
    Livewire::test(VacationBalanceTotalBox::class)
        ->assertOk();
});

test('shows vacation balance for employed employees', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->startOfYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->startOfYear(),
        'valid_until' => null,
        'annual_vacation_days' => 30,
    ]);

    $component = Livewire::test(VacationBalanceTotalBox::class)
        ->assertOk();

    expect($component->get('sum'))->toContain(__('days'))
        ->and($component->get('subValue'))->toContain(__('days'));
});

test('shows zero when no employees exist', function (): void {
    $component = Livewire::test(VacationBalanceTotalBox::class)
        ->assertOk();

    expect($component->get('sum'))->toContain('0');
});
