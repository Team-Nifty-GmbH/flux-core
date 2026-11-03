<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Enums\PaymentIntervalEnum;
use FluxErp\Enums\SalaryTypeEnum;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\SalaryComponent;
use FluxErp\Models\WorkTimeModel;

beforeEach(function (): void {
    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
});

test('monthly salary with components and employer contributions', function (): void {
    $this->employee->update([
        'salary_type' => SalaryTypeEnum::Monthly,
        'base_salary' => 3000,
        'employer_contribution_rate' => 20,
    ]);

    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Allowance',
        'amount' => 50,
        'payment_interval' => PaymentIntervalEnum::Monthly,
    ]);
    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Bonus',
        'amount' => 1200,
        'payment_interval' => PaymentIntervalEnum::Annual,
    ]);

    $costs = $this->employee->refresh()->getMonthlyPersonnelCosts();

    expect(bccomp($costs['gross'], '3150', 2))->toBe(0)
        ->and(bccomp($costs['employer_contributions'], '630', 2))->toBe(0)
        ->and(bccomp($costs['total'], '3780', 2))->toBe(0);
});

test('annual salary is spread over twelve months', function (): void {
    $this->employee->update([
        'salary_type' => SalaryTypeEnum::Annual,
        'base_salary' => 60000,
    ]);

    $costs = $this->employee->refresh()->getMonthlyPersonnelCosts();

    expect(bccomp($costs['gross'], '5000', 2))->toBe(0)
        ->and(bccomp($costs['employer_contributions'], '0', 2))->toBe(0);
});

test('hourly salary uses the weekly hours of the work time model', function (): void {
    $workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Part time 20h',
        'weekly_hours' => 20,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);
    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $this->employee->getKey(),
        'work_time_model_id' => $workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
    ]);

    $this->employee->update([
        'salary_type' => SalaryTypeEnum::Hourly,
        'hourly_rate' => 15,
    ]);

    $costs = $this->employee->refresh()->getMonthlyPersonnelCosts();

    expect(bccomp($costs['gross'], '1300', 2))->toBe(0);
});

test('only components valid at the date count', function (): void {
    $this->employee->update([
        'salary_type' => SalaryTypeEnum::Monthly,
        'base_salary' => 1000,
    ]);

    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Expired',
        'amount' => 100,
        'payment_interval' => PaymentIntervalEnum::Monthly,
        'valid_until' => now()->subDay(),
    ]);
    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Future',
        'amount' => 100,
        'payment_interval' => PaymentIntervalEnum::Monthly,
        'valid_from' => now()->addDay(),
    ]);
    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Ends today',
        'amount' => 10,
        'payment_interval' => PaymentIntervalEnum::Monthly,
        'valid_until' => now(),
    ]);
    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Current',
        'amount' => 300,
        'payment_interval' => PaymentIntervalEnum::Quarterly,
        'valid_from' => now()->subMonth(),
        'valid_until' => now()->addMonth(),
    ]);

    $costs = $this->employee->refresh()->getMonthlyPersonnelCosts();

    expect(bccomp($costs['gross'], '1110', 2))->toBe(0);
});

test('salary data is hidden from serialization', function (): void {
    $this->employee->update([
        'base_salary' => 3000,
        'hourly_rate' => 20,
        'employer_contribution_rate' => 20,
    ]);
    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Allowance',
        'amount' => 50,
        'payment_interval' => PaymentIntervalEnum::Monthly,
    ]);

    $array = $this->employee->refresh()->load('salaryComponents')->toArray();

    expect($array)
        ->not->toHaveKeys(['base_salary', 'hourly_rate', 'employer_contribution_rate', 'salary_components']);
});
