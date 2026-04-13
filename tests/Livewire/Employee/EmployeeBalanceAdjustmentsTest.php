<?php

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Livewire\Employee\EmployeeBalanceAdjustments;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeBalanceAdjustment;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeBalanceAdjustments::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(EmployeeBalanceAdjustments::class, ['employeeId' => $employee->getKey()])
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('employeeBalanceAdjustmentForm.id', null)
        ->assertSet('employeeBalanceAdjustmentForm.amount', null)
        ->assertSet('employeeBalanceAdjustmentForm.type', null)
        ->assertOpensModal('employee-balance-adjustment-form-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $adjustment = app(EmployeeBalanceAdjustment::class)->create([
        'employee_id' => $employee->getKey(),
        'type' => EmployeeBalanceAdjustmentTypeEnum::Vacation->value,
        'amount' => 5,
        'effective_date' => now()->format('Y-m-d'),
        'reason' => EmployeeBalanceAdjustmentReasonEnum::Correction,
        'description' => 'Test adjustment',
    ]);

    Livewire::test(EmployeeBalanceAdjustments::class, ['employeeId' => $employee->getKey()])
        ->call('edit', $adjustment->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('employeeBalanceAdjustmentForm.id', $adjustment->getKey())
        ->assertSet('employeeBalanceAdjustmentForm.description', 'Test adjustment')
        ->assertOpensModal('employee-balance-adjustment-form-modal');
});

test('can create balance adjustment via save', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(EmployeeBalanceAdjustments::class, ['employeeId' => $employee->getKey()])
        ->call('edit')
        ->set('employeeBalanceAdjustmentForm.type', EmployeeBalanceAdjustmentTypeEnum::Vacation->value)
        ->set('employeeBalanceAdjustmentForm.amount', 3)
        ->set('employeeBalanceAdjustmentForm.effective_date', now()->format('Y-m-d'))
        ->set('employeeBalanceAdjustmentForm.reason', EmployeeBalanceAdjustmentReasonEnum::Correction)
        ->set('employeeBalanceAdjustmentForm.description', 'Created via test')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('employee_balance_adjustments', [
        'employee_id' => $employee->getKey(),
        'type' => EmployeeBalanceAdjustmentTypeEnum::Vacation->value,
        'description' => 'Created via test',
    ]);
});

test('can update balance adjustment via save', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $adjustment = app(EmployeeBalanceAdjustment::class)->create([
        'employee_id' => $employee->getKey(),
        'type' => EmployeeBalanceAdjustmentTypeEnum::Vacation->value,
        'amount' => 5,
        'effective_date' => now()->format('Y-m-d'),
        'reason' => EmployeeBalanceAdjustmentReasonEnum::Correction,
        'description' => 'Original description',
    ]);

    Livewire::test(EmployeeBalanceAdjustments::class, ['employeeId' => $employee->getKey()])
        ->call('edit', $adjustment->getKey())
        ->set('employeeBalanceAdjustmentForm.description', 'Updated description')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($adjustment->refresh()->description)->toEqual('Updated description');
});

test('save validates required fields', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(EmployeeBalanceAdjustments::class, ['employeeId' => $employee->getKey()])
        ->call('edit')
        ->set('employeeBalanceAdjustmentForm.type', null)
        ->set('employeeBalanceAdjustmentForm.amount', null)
        ->call('save')
        ->assertReturned(false);
});
