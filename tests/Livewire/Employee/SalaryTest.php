<?php

use FluxErp\Enums\PaymentIntervalEnum;
use FluxErp\Enums\SalaryTypeEnum;
use FluxErp\Livewire\DataTables\EmployeeList;
use FluxErp\Livewire\Employee\Employee as EmployeeView;
use FluxErp\Livewire\Employee\Salary;
use FluxErp\Models\Activity;
use FluxErp\Models\Employee;
use FluxErp\Models\Permission;
use Illuminate\Support\Number;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear()->format('Y-m-d'),
        'salary_type' => SalaryTypeEnum::Monthly,
        'base_salary' => 2000,
        'employer_contribution_rate' => 10,
    ]);
});

test('renders successfully with the salary of the employee', function (): void {
    Livewire::test(Salary::class, ['employeeId' => $this->employee->getKey()])
        ->assertOk()
        ->assertSet('employeeSalaryForm.base_salary', '2000.00')
        ->assertSet('costs.total', Number::currency(2200));
});

test('saves salary and recalculates costs', function (): void {
    Livewire::test(Salary::class, ['employeeId' => $this->employee->getKey()])
        ->set('employeeSalaryForm.base_salary', 2500)
        ->call('saveSalary')
        ->assertHasNoErrors()
        ->assertReturned(true)
        ->assertSet('costs.total', Number::currency(2750));

    expect(bccomp($this->employee->refresh()->base_salary, '2500', 2))->toBe(0);
});

test('creates salary component and recalculates costs', function (): void {
    Livewire::test(Salary::class, ['employeeId' => $this->employee->getKey()])
        ->call('edit')
        ->set('salaryComponentForm.name', 'Allowance')
        ->set('salaryComponentForm.amount', 100)
        ->set('salaryComponentForm.payment_interval', PaymentIntervalEnum::Monthly->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true)
        ->assertSet('costs.gross', Number::currency(2100));

    $this->assertDatabaseHas('salary_components', [
        'employee_id' => $this->employee->getKey(),
        'name' => 'Allowance',
    ]);
});

test('saving the general tab keeps the salary', function (): void {
    Livewire::test(EmployeeView::class, ['id' => $this->employee->getKey()])
        ->set('employee.job_title', 'Developer')
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->employee->refresh();

    expect($this->employee->job_title)->toBe('Developer')
        ->and(bccomp($this->employee->base_salary, '2000', 2))->toBe(0)
        ->and(bccomp($this->employee->employer_contribution_rate, '10', 2))->toBe(0);
});

test('employee list does not offer salary columns', function (string $column): void {
    expect(Livewire::test(EmployeeList::class)->instance()->getAvailableCols())
        ->not->toContain($column);
})
    ->with(['base_salary', 'hourly_rate', 'employer_contribution_rate']);

test('employee list does not aggregate, sort or filter by salary', function (): void {
    $component = Livewire::test(EmployeeList::class)
        ->set('enabledCols', ['id', 'name', 'base_salary'])
        ->set('aggregatableCols', ['sum' => [], 'avg' => [], 'min' => [], 'max' => ['base_salary'], 'count' => []])
        ->set('userOrderBy', 'base_salary')
        ->set('userFilters', [[['column' => 'base_salary', 'operator' => '>', 'value' => 1999]]])
        ->call('loadData')
        ->assertOk();

    expect($component->get('enabledCols'))->not->toContain('base_salary')
        ->and($component->get('userOrderBy'))->toBe('')
        ->and($component->instance()->getDataForTesting())->not->toHaveKey('aggregates.max.base_salary')
        ->and(json_encode($component->instance()->getDataForTesting()))->not->toContain('2000')
        ->and(json_encode($component->get('userFilters')))->not->toContain('base_salary');
});

test('salary changes are not written to the activity log', function (): void {
    $this->employee->update(['base_salary' => 3000, 'job_title' => 'Developer']);

    $properties = Activity::query()
        ->where('subject_type', $this->employee->getMorphClass())
        ->where('subject_id', $this->employee->getKey())
        ->pluck('attribute_changes')
        ->toJson();

    expect($properties)->toContain('Developer')
        ->not->toContain('base_salary')
        ->not->toContain('employer_contribution_rate');
});

test('salary tab requires its tab permission', function (): void {
    Permission::findOrCreate('tab.employee.salary', 'web');

    Livewire::test(Salary::class, ['employeeId' => $this->employee->getKey()])
        ->assertForbidden();
});
