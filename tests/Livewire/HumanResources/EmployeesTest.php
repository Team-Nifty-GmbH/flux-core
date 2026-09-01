<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\HumanResources\Employees;
use FluxErp\Models\Employee;
use FluxErp\Models\WorkTimeModel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Employees::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Employees::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('employeeForm.id', null)
        ->assertSet('employeeForm.firstname', null)
        ->assertSet('employeeForm.lastname', null)
        ->assertOpensModal('employee-form-modal');
});

test('edit with id redirects to detail route', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(Employees::class)
        ->call('edit', $employee->getKey())
        ->assertRedirect();
});

test('can create employee via save', function (): void {
    $workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $newUser = FluxErp\Models\User::factory()->create([
        'is_active' => true,
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    Livewire::test(Employees::class)
        ->call('edit')
        ->set('employeeForm.firstname', 'New')
        ->set('employeeForm.lastname', 'Employee')
        ->set('employeeForm.tenant_id', $this->dbTenant->getKey())
        ->set('employeeForm.user_id', $newUser->getKey())
        ->set('employeeForm.employment_date', now()->format('Y-m-d'))
        ->set('employeeForm.work_time_model_id', $workTimeModel->getKey())
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('employees', [
        'firstname' => 'New',
        'lastname' => 'Employee',
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
});

test('save validates required fields', function (): void {
    Livewire::test(Employees::class)
        ->call('edit')
        ->set('employeeForm.firstname', null)
        ->set('employeeForm.lastname', null)
        ->call('save')
        ->assertReturned(false);
});
