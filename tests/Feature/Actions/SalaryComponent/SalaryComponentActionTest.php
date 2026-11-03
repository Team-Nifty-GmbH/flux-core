<?php

use FluxErp\Actions\SalaryComponent\CreateSalaryComponent;
use FluxErp\Actions\SalaryComponent\DeleteSalaryComponent;
use FluxErp\Actions\SalaryComponent\UpdateSalaryComponent;
use FluxErp\Enums\PaymentIntervalEnum;
use FluxErp\Models\Employee;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
});

test('create, update and delete salary component', function (): void {
    $salaryComponent = CreateSalaryComponent::make([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Allowance',
        'amount' => 50,
        'payment_interval' => PaymentIntervalEnum::Monthly->value,
    ])
        ->validate()
        ->execute();

    expect($salaryComponent->payment_interval)->toBe(PaymentIntervalEnum::Monthly);

    $salaryComponent = UpdateSalaryComponent::make([
        'id' => $salaryComponent->getKey(),
        'amount' => 75,
    ])
        ->validate()
        ->execute();

    expect(bccomp($salaryComponent->amount, '75', 2))->toBe(0);

    DeleteSalaryComponent::make(['id' => $salaryComponent->getKey()])
        ->validate()
        ->execute();

    $this->assertSoftDeleted('salary_components', ['id' => $salaryComponent->getKey()]);
});

test('create rejects unknown interval and inverted validity', function (array $data): void {
    CreateSalaryComponent::make(array_merge([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Allowance',
        'amount' => 50,
        'payment_interval' => PaymentIntervalEnum::Monthly->value,
    ], $data))
        ->validate();
})
    ->with([
        'interval' => [['payment_interval' => 'daily']],
        'validity' => [['valid_from' => '2026-02-01', 'valid_until' => '2026-01-01']],
    ])
    ->throws(ValidationException::class);
